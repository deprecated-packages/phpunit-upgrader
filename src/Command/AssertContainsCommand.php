<?php

declare(strict_types=1);

namespace Symplify\PHPUnitUpgrader\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use Symplify\PHPUnitUpgrader\PHPUnitUpgrader\AssertContainsPHPUnitUpgrader;
use Symplify\PHPUnitUpgrader\ValueObject\Option;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AssertContainsCommand extends AbstractSymplifyCommand
{
    public function __construct(
        private AssertContainsPHPUnitUpgrader $assertContainsPHPUnitUpgrader
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Change assertContains() to assertStringContainsString() where reported by PHPUnit');
        $this->addArgument(
            Option::SOURCE,
            InputArgument::REQUIRED,
            'Path to error output from PHPUnit with assertContains() report'
        );

        $this->addOption(
            Option::ERROR_REPORT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to PHPUnit report file to extract assertString() failure from'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (string) $input->getArgument(Option::SOURCE);
        $this->fileSystemGuard->ensureFileExists($source, __METHOD__);

        $testFileInfos = $this->smartFinder->find([$source], '#Test\.php#');

        $errorReportFile = (string) $input->getOption(Option::ERROR_REPORT_FILE);
        $this->fileSystemGuard->ensureFileExists($errorReportFile, __METHOD__);

        $errorReportFileInfo = new SmartFileInfo($errorReportFile);
        $this->assertContainsPHPUnitUpgrader->renameFileInfos($testFileInfos, $errorReportFileInfo);

        $this->symfonyStyle->success('assertContains() was converted to assertStringContainsString() where needed');

        return self::SUCCESS;
    }
}
