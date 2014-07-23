<?php

namespace Pim\Bundle\VersioningBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Monolog\Handler\StreamHandler;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Refresh versioning data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshCommand extends ContainerAwareCommand
{
    /**
     * Versioned entities
     */
    protected $versionedEntities = array();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:versioning:refresh')
            ->setDescription('Version any updated entities')
            ->addOption(
                'show-log',
                null,
                InputOption::VALUE_OPTIONAL,
                'display the log on the output'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'flush new versions by using this batch size',
                100
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDebug = $input->getOption('no-debug');
        if (!$noDebug) {
            $logger = $this->getContainer()->get('logger');
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }
        $totalPendings = (int) $this->getVersionManager()
            ->getVersionRepository()
            ->getPendingVersionsCount();

        if ($totalPendings === 0) {
            $output->writeln('<info>Versioning is already up to date.</info>');
            return;
        }

        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalPendings);

        $batchSize = $input->getOption('batch-size');

        $om = $this->getObjectManager();

        $pendingVersions = $this->getVersionManager()
            ->getVersionRepository()
            ->getPendingVersions($batchSize);

        $nbPendings = count($pendingVersions);

        while ($nbPendings > 0) {

            $previousVersions = [];
            foreach ($pendingVersions as $pending) {
                $key = sprintf('%s_%s', $pending->getResourceName(), $pending->getResourceId());

                $previousVersion = isset($previousVersions[$key]) ? $previousVersions[$key] : null;
                $version = $this->createVersion($pending, $previousVersion);

                if ($version) {
                    $previousVersions[$key] = $version;
                }

                $progress->advance();

            }
            $om->flush();
            $om->clear('Pim\\Bundle\\VersioningBundle\\Entity\\Version');

            $pendingVersions = $this->getVersionManager()
                ->getVersionRepository()
                ->getPendingVersions($batchSize);
            $nbPendings = count($pendingVersions);
        }
        $progress->finish();
        $output->writeln(sprintf('<info>%d created versions.</info>', $totalPendings));
    }

    /**
     * @param Version $version
     * @param Version $previousVersion
     *
     * @return Version|null
     */
    protected function createVersion(Version $version, Version $previousVersion = null)
    {
        $version = $this->getVersionManager()->buildPendingVersion($version, $previousVersion);

        if ($version->getChangeset()) {
            $this->getObjectManager()->persist($version);

            return $version;
        } else {
            $this->getObjectManager()->remove($version);
        }
    }

    /**
     * @return AddVersionListener
     */
    protected function getVersionManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        $versionClass = $this->getContainer()->getParameter('pim_versioning.entity.version.class');

        return $this
            ->getContainer()
            ->get('pim_catalog.doctrine.smart_manager_registry')
            ->getManagerForClass($versionClass);
    }
}
