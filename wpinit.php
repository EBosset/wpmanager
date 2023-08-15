#!/usr/bin/env php

<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WpConfig
{
    public string $locale;
    public string $dbName;
    public string $dbUser;
    public string $dbPass;
    public string $dbHost;
    public string $tablePrefix;
    public string $siteTitle;
    public string $username;
    public string $password;
    public string $email;

    public function __construct(
        string $locale,
        string $dbName,
        string $dbUser,
        string $dbPass,
        string $dbHost,
        string $tablePrefix,
        string $siteTitle,
        string $username,
        string $password,
        string $email
    ) {
        $this->locale = $locale;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->dbHost = $dbHost;
        $this->tablePrefix = $tablePrefix;
        $this->siteTitle = $siteTitle;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
    }

    public static function createFromCliInput(SymfonyStyle $io, InputInterface $input): WpConfig
    {
        $locale = $input->getOption('locale');
        $confirm = self::confirmArg($io, 'Site language', $locale);
        if ($confirm) {
            $locale = null;
            while (!$locale) {
                $locale = $io->ask('Site language ( locale_region )');
            }
        }

        $dbName = null;
        if (!$dbName) {
            while (!$dbName) {
                # Todo : database name conflict management, drop database ?
                $dbName = $io->ask('Database Name (The name of the database you want to use with WordPress.)');
            }
        }

        $dbUser = $input->getOption('db-user');
        if (!$dbUser) {
            while (!$dbUser) {
                $dbUser = $io->ask('Username (Your database username.)');
            }
        }

        $dbPass = $input->getOption('db-pass');
        if (!$dbPass) {
            while (!$dbPass) {
                $dbPass = $io->askHidden('Password (Your database password.)');
            }
        }

        $dbHost = $input->getOption('db-host');
        $confirm = self::confirmArg($io, 'Database Host', $dbHost);
        if ($confirm) {
            $dbHost = null;
            while (!$dbHost) {
                $dbHost = $io->ask('Database Host (You should be able to get this info from your web host, if localhost does not work. .)');
            }
        }

        $tablePrefix = $input->getOption('table-prefix');
        $confirm = self::confirmArg($io, 'Table Prefix', $tablePrefix);
        if ($confirm) {
            $tablePrefix = null;
            while (!$tablePrefix) {
                $tablePrefix = $io->ask('Table Prefix (If you want to run multiple WordPress installations in a single database, change this.)');
            }
        }

        #

        $siteTitle = $input->getOption('title');
        if (!$siteTitle) {
            while (!$siteTitle) {
                $siteTitle = $io->ask('Site Title:');
            }
        }

        $username = $input->getOption('username');
        if (!$username) {
            while (!$username) {
                $username = $io->ask('Username (Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.):');
            }
        }

        $password = $input->getOption('password');
        if (!$password) {
            while (!$password) {
                $password = $io->askHidden('Password (You will need this password to log in. Please store it in a secure location.):');
            }
        }

        $email = $input->getOption('email');
        if (!$email) {
            while (!$email) {
                $email = $io->ask('Email (Double-check your email address before continuing.):');
            }
        }

        return new WpConfig(
            $locale,
            $dbName,
            $dbUser,
            $dbPass,
            $dbHost,
            $tablePrefix,
            $siteTitle,
            $username,
            $password,
            $email
        );
    }

    public static function createFromQuestions(SymfonyStyle $io): WpConfig
    {
        $locale = null;
        while (!$locale) {
            $locale = $io->ask('Database Name (The name of the database you want to use with WordPress.)');
        }

        $dbName = null;
        while (!$dbName) {
            $dbName = $io->ask('Database Name (The name of the database you want to use with WordPress.)');
        }

        $dbUser = null;
        while (!$dbUser) {
            $dbUser = $io->ask('Username (Your database username.)');
        }

        $dbPass = null;
        while (!$dbPass) {
            $dbPass = $io->askHidden('Password (Your database password.)');
        }

        $dbHost = null;
        while (!$dbHost) {
            $dbHost = $io->ask('Database Host (You should be able to get this info from your web host, if localhost does not work. .)');
        }

        $tablePrefix = null;
        while (!$tablePrefix) {
            $tablePrefix = $io->ask('Table Prefix (If you want to run multiple WordPress installations in a single database, change this.)');
        }

        #

        $siteTitle = null;
        while (!$siteTitle) {
            $siteTitle = $io->ask('Site Title');
        }

        $username = null;
        while (!$username) {
            $username = $io->ask('Username (Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.)');
        }

        $password = null;
        while (!$password) {
            $password = $io->askHidden('Password (You will need this password to log in. Please store it in a secure location.)');
        }

        $email = null;
        while (!$email) {
            $email = $io->ask('Email (Double-check your email address before continuing.)');
        }

        return new WpConfig(
            $locale,
            $dbName,
            $dbUser,
            $dbPass,
            $dbHost,
            $tablePrefix,
            $siteTitle,
            $username,
            $password,
            $email
        );
    }

    private static function confirmArg(SymfonyStyle $io, string $argName, string $currentValue): bool
    {
        $io->write("Current $argName : $currentValue.");
        $changeQuestion = new ConfirmationQuestion(
            "Do you want change $argName ?",
            false,
            '/^(y|j|o)/i'
        );
        return $io->askQuestion($changeQuestion);
    }
}

function existExecutable(string $executable): bool
{
    $wpCliExistProcess = new Process(['which', $executable]);
    $wpCliExistProcess->run();

    if (!$wpCliExistProcess->isSuccessful()) {
        throw new ProcessFailedException($wpCliExistProcess);
    }

    $wpCliExistProcessOutput = $wpCliExistProcess->getOutput();
    if ($wpCliExistProcessOutput[0] !== '/') {
        return false;
    }

    return true;
}

function wpCliExist(): bool
{
    return existExecutable('wp');
}

function mysqlExist(): bool
{
    return existExecutable('mysql');
}

function mysqlIsRunning(): bool
{
    $mysqlGetPIDProcess = Process::fromShellCommandline('pgrep mysql | wc -l');
    $mysqlGetPIDProcess->run();

    if (!$mysqlGetPIDProcess->isSuccessful()) {
        throw new ProcessFailedException($mysqlGetPIDProcess);
    }

    return '0' !== $mysqlGetPIDProcess->getOutput()[0];
}

/**
 * @throws Exception
 */
function checkSystemRequirement(): void
{
    if (!wpCliExist()) {
        throw new Exception("wp-cli is required, you can install it here : \nVoir: https://wp-cli.org/fr/");
    }

    if (!mysqlExist()) {
        throw new Exception("mysql is required, you can install it here \nVoir: https://doc.ubuntu-fr.org/mysql");
    }

    if (!mysqlIsRunning()) {
        $startMysqlProcess = Process::fromShellCommandline('sudo service mysql start');
        $startMysqlProcess->run();

        if (!$startMysqlProcess->isSuccessful()) {
            throw new ProcessFailedException($startMysqlProcess);
        }
    }
}

/**
 * @throws Exception
 */
function command(InputInterface $input, OutputInterface $output): int
{
    $intro = '
                ██╗    ██╗██████╗ ███╗   ███╗
                ██║    ██║██╔══██╗████╗ ████║
                ██║ █╗ ██║██████╔╝██╔████╔██║
                ██║███╗██║██╔═══╝ ██║╚██╔╝██║
                ╚███╔███╔╝██║     ██║ ╚═╝ ██║
                 ╚══╝╚══╝ ╚═╝     ╚═╝     ╚═╝
                      WordPress Manager
    ';
    echo $intro;

    $io = new SymfonyStyle($input, $output);

    try {
        checkSystemRequirement();
    } catch (Exception $e) {
        $io->error($e->getMessage());
        return Command::FAILURE;
    }

    $wpConfig = WpConfig::createFromCliInput($io, $input);
    displayRecapConfig($output, $wpConfig);

    $confirm = false;
    while (!$confirm) {
        $io->write("Your Wordpress will be installed with this configuration.");
        $changeQuestion = new ConfirmationQuestion(
            "Are you OK ?",
            true,
            '/^(y|j|o)/i'
        );
        $confirm = $io->askQuestion($changeQuestion);
        if (!$confirm) {
            $wpConfig = WpConfig::createFromQuestions($io);
        }
    }

    $path = null;
    $io->write('Create directory ( named like site title )');
    $directoryCreated = false;
    $directoryName = $wpConfig->siteTitle;
    while (!$directoryCreated) {
        $path = str_replace(' ', '-', $directoryName);

        $filesystem = new Filesystem();
        if (!$filesystem->exists($path)) {
            $filesystem->mkdir($path);
            $directoryCreated = true;
            $io->info("$path has been created");
        } else {
            $io->error("Directory $directoryName already exist");
            $directoryName = $io->ask('Please choose another directory name');
        }
    }

    if (!$path) {
        throw new Exception('Une eruption solaire a effacé ma variable !!!');
    }

    $io->write('Download WP core into the directory');
    $process = Process::fromShellCommandline("cd $path && wp core download --locale=$wpConfig->locale");
    $process->run();
    $io->write($process->getOutput());

    $io->write('Generate wp-config file.');
    $process = Process::fromShellCommandline(
        "cd $path && wp core config --dbname=$wpConfig->dbName --dbuser=$wpConfig->dbUser --dbpass=$wpConfig->dbPass --dbprefix=$wpConfig->tablePrefix"
    );
    $process->run();
    $io->write($process->getOutput());

    $io->write('Check database.');
    if (databaseExist($path)) {
        $io->warning('Database '. $wpConfig->dbName . ' already exists!');

        $choice = $io->choice('What you want to do ?', ['erase old database', 'choose another database name']);
        if ('choose another database name' === $choice) {
            $dbNameExist = true;
            while ($dbNameExist) {
                $newDbName = $io->ask('New database name');
                $wpConfig->dbName = $newDbName;
                $process = Process::fromShellCommandline(
                    "cd $path && wp config set DB_NAME $newDbName"
                );
                $process->run();
                $dbNameExist = databaseExist($path);
                if ($dbNameExist) {
                    $io->write('This database name already exists too..');
                }
            }
        } else {
            $io->write('Drop old database.');
            $process = Process::fromShellCommandline(
                "cd $path && wp db drop"
            );
            $process->run();
            $io->write($process->getOutput());
        }
    }

    $io->write('Create database.');
    $process = Process::fromShellCommandline(
        "cd $path && wp db create"
    );
    $process->run();
    $io->write($process->getOutput());

    $io->write('Install wp admin .');
    $wpInstallProcess = Process::fromShellCommandline("cd $path && wp core install --url=$wpConfig->dbHost --title='$wpConfig->siteTitle' --admin_name=$wpConfig->username --admin_email=$wpConfig->email --admin_password=$wpConfig->password");
    $wpInstallProcess->run();
    $io->write($wpInstallProcess->getOutput());


    $io->success("Wordpress is ready, run it with this command : 'cd $path && wp server'");

    return Command::SUCCESS;
}

function databaseExist(string $path): bool
{
    $process = Process::fromShellCommandline(
        "cd $path && wp db check"
    );
    $process->run();

    return (bool) preg_match('/Success: Database checked/', $process->getOutput());
}

function displayRecapConfig(OutputInterface $output, WpConfig $wpConfig): void
{
    $table = new Table($output);
    $table
        ->setHeaders([
            'locale',
            'dbName',
            'dbUser',
            'dbPass',
            'dbHost',
            'tablePrefix',
            'siteTitle',
            'username',
            'password',
            'email',
        ])
        ->setRows([
            [
                $wpConfig->locale,
                $wpConfig->dbName,
                $wpConfig->dbUser,
                $wpConfig->dbPass,
                $wpConfig->dbHost,
                $wpConfig->tablePrefix,
                $wpConfig->siteTitle,
                $wpConfig->username,
                $wpConfig->password,
                $wpConfig->email,
            ],
        ])
        ->setHorizontal()
    ;
    $table->render();
}

try {
    (new SingleCommandApplication())
        ->setName('Wp Manager')
        ->addOption(
            'locale', 'l',
            InputOption::VALUE_OPTIONAL,
            'WP locale for the project',
            'fr_FR'
        )
        ->addOption(
            'db-user', 'u',
            InputOption::VALUE_OPTIONAL,
            'Mysql user',
            null
        )
        ->addOption(
            'db-pass', 'p',
            InputOption::VALUE_OPTIONAL,
            'Mysql password',
            null
        )
        ->addOption(
            'table-prefix', 'x',
            InputOption::VALUE_OPTIONAL,
            'Prefix for wordpress tables',
            'wp_'
        )
        ->addOption(
            'db-host', 'o',
            InputOption::VALUE_OPTIONAL,
            'Project host',
            'localhost'
        )
        ->addOption(
            'title', 't',
            InputOption::VALUE_OPTIONAL,
            'Site title',
            null
        )
        ->addOption(
            'username', 'y',
            InputOption::VALUE_OPTIONAL,
            'Site admin username',
            null
        )
        ->addOption(
            'password', 's',
            InputOption::VALUE_OPTIONAL,
            'Site admin password',
            null
        )
        ->addOption(
            'email', 'm',
            InputOption::VALUE_OPTIONAL,
            'Site admin email',
            null
        )
        ->setCode('command')
        ->run();
} catch (ExceptionInterface $e) {
}
