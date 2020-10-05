<?php

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Process\Process;

class DatabaseContext implements Context
{
    const DB_DRIVER_FUNC = [
        'pdo_mysql' => 'clearMySQLDatabase',
        'pdo_pgsql' => 'clearPgSQLDatabase',
    ];

    protected ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @BeforeScenario @clearDatabase
     * @Given the database is clear
     */
    public function clearDatabase(?BeforeScenarioScope $scope = null)
    {
        if (!$this->migrate()) {
            /** @var Connection $conn */
            $conn = $this->doctrine->getConnection();

            foreach (self::DB_DRIVER_FUNC as $driverName => $func) {
                if ($driverName === $conn->getDriver()->getName()) {
                    $this->$func($conn);
                }
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function migrate(): bool
    {
        static $done = false;

        $return = !$done;

        if ($return) {
            $process = Process::fromShellCommandline('APP_ENV=test bin/console doctrine:schema:drop --force --full-database --env=test');
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception(
                    sprintf('The doctrine schema drop fail to execute. Message: "%s"', $process->getErrorOutput())
                );
            }

            $process = Process::fromShellCommandline('APP_ENV=test bin/console doctrine:migration:migrate --no-interaction --env=test');
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception(
                    sprintf('The doctrine migration fail to execute. Message: "%s"', $process->getErrorOutput())
                );
            }

            $done = true;
        }

        return $return;
    }

    /**
     * @param Connection $conn
     *
     * @throws \Throwable
     */
    private function clearMySQLDatabase(Connection $conn)
    {
        if (!$this->migrate()) {
            self::truncateMysqlDatabase($conn);
        }
    }

    /**
     * @param Connection $conn
     *
     * @throws \Throwable
     */
    private function clearPgSQLDatabase(Connection $conn)
    {
        if (!$this->migrate()) {
            self::deletePgsqlDatabase($conn);
        }
    }

    /**
     * @param Connection $connection
     *
     * @throws \Throwable
     */
    private static function truncateMysqlDatabase(Connection $connection)
    {
        $connection->transactional(
            function (Connection $connection) {
                $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
                foreach ($connection->getSchemaManager()->listTableNames() as $tableName) {
                    $connection->executeQuery(sprintf('TRUNCATE TABLE %s', $tableName));
                }
                $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
            }
        );
    }

    /**
     * @param Connection $connection
     *
     * @throws \Throwable
     */
    private static function truncatePgsqlDatabase(Connection $connection)
    {
        $connection->transactional(function (Connection $connection) {
            foreach ($connection->getSchemaManager()->listTableNames() as $tableName) {
                $connection->executeQuery(sprintf('TRUNCATE TABLE %s CASCADE', $tableName));
            }
        });
    }

    /**
     * @param Connection $connection
     *
     * @throws \Throwable
     */
    private static function deletePgsqlDatabase(Connection $connection)
    {
        $connection->transactional(function (Connection $connection) {
            $connection->executeQuery('SET session_replication_role = \'replica\'');
            foreach ($connection->getSchemaManager()->listTableNames() as $tableName) {
                $connection->executeQuery(sprintf('DELETE FROM %s', $tableName));
            }
            $connection->executeQuery('SET session_replication_role = \'origin\'');
        });
    }

    /**
     * @Given the fixtures for group :groupName are loaded
     */
    public function theFixturesForGroupAreLoaded($groupName)
    {
        $process = Process::fromShellCommandline("APP_ENV=test bin/console doctrine:fixtures:load --group=$groupName --append --no-interaction --env=test");
        $process->run();
        if (!$process->isSuccessful()) {
            dump($process->getErrorOutput());
            throw new \Exception("Fixtures of group $groupName cannot be loaded.");
        };
    }

}
