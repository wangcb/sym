<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Component\Translation\Catalogue\DiffOperation;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * A command that parse templates to extract translation messages and add them into the translation files.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class TranslationUpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('translation:update')
            ->setDefinition(array(
                new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle where to load the messages'),
                new InputOption('prefix', null, InputOption::VALUE_OPTIONAL, 'Override the default prefix', '__'),
                new InputOption('output-format', null, InputOption::VALUE_OPTIONAL, 'Override the default output format', 'yml'),
                new InputOption('dump-messages', null, InputOption::VALUE_NONE, 'Should the messages be dumped in the console'),
                new InputOption('force', null, InputOption::VALUE_NONE, 'Should the update be done'),
                new InputOption('clean', null, InputOption::VALUE_NONE, 'Should clean not found messages'),
            ))
            ->setDescription('Updates the translation file')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command extracts translation strings from templates
of a given bundle. It can display them or merge the new ones into the translation files.
When new translation strings are found it can automatically add a prefix to the translation
message.

  <info>php %command.full_name% --dump-messages en AcmeBundle</info>
  <info>php %command.full_name% --force --prefix="new_" fr AcmeBundle</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getContainer()->get('kernel');

        // check presence of force or dump-message
        if ($input->getOption('force') !== true && $input->getOption('dump-messages') !== true) {
            $output->writeln('<info>You must choose one of --force or --dump-messages</info>');

            return 1;
        }

        // check format
        $writer = $this->getContainer()->get('translation.writer');
        $supportedFormats = $writer->getFormats();
        if (!in_array($input->getOption('output-format'), $supportedFormats)) {
            $output->writeln('<error>Wrong output format</error>');
            $output->writeln('Supported formats are '.implode(', ', $supportedFormats).'.');

            return 1;
        }

        // get bundle directory
        $foundBundle = $kernel->getBundle($input->getArgument('bundle'));
        $bundleTransPaths = array(
            $foundBundle->getPath().'/Resources/',
            sprintf('%s/Resources/%s/', $kernel->getRootDir(), $foundBundle->getName()),
        );

        $output->writeln(sprintf('Generating "<info>%s</info>" translation files for "<info>%s</info>"', $input->getArgument('locale'), $foundBundle->getName()));

        // load any messages from templates
        $extractedCatalogue = new MessageCatalogue($input->getArgument('locale'));
        $output->writeln('Parsing templates');
        $extractor = $this->getContainer()->get('translation.extractor');
        $extractor->setPrefix($input->getOption('prefix'));
        foreach ($bundleTransPaths as $path) {
            $path .= 'views';
            if (is_dir($path)) {
                $extractor->extract($path, $extractedCatalogue);
            }
        }

        // load any existing messages from the translation files
        $currentCatalogue = new MessageCatalogue($input->getArgument('locale'));
        $output->writeln('Loading translation files');
        $loader = $this->getContainer()->get('translation.loader');
        foreach ($bundleTransPaths as $path) {
            $path .= 'translations';
            if (is_dir($path)) {
                $loader->loadMessages($path, $currentCatalogue);
            }
        }

        // process catalogues
        $operation = $input->getOption('clean')
            ? new DiffOperation($currentCatalogue, $extractedCatalogue)
            : new MergeOperation($currentCatalogue, $extractedCatalogue);

        // Exit if no messages found.
        if (!count($operation->getDomains())) {
            $output->writeln("\n<comment>No translation found.</comment>");

            return;
        }

        // show compiled list of messages
        if ($input->getOption('dump-messages') === true) {
            foreach ($operation->getDomains() as $domain) {
                $output->writeln(sprintf("\nDisplaying messages for domain <info>%s</info>:\n", $domain));
                $newKeys = array_keys($operation->getNewMessages($domain));
                $allKeys = array_keys($operation->getMessages($domain));
                foreach (array_diff($allKeys, $newKeys) as $id) {
                    $output->writeln($id);
                }
                foreach ($newKeys as $id) {
                    $output->writeln(sprintf('<fg=green>%s</>', $id));
                }
                foreach (array_keys($operation->getObsoleteMessages($domain)) as $id) {
                    $output->writeln(sprintf('<fg=red>%s</>', $id));
                }
            }

            if ($input->getOption('output-format') == 'xlf') {
                $output->writeln('Xliff output version is <info>1.2</info>');
            }
        }

        // save the files
        if ($input->getOption('force') === true) {
            $output->writeln('Writing files');
            $bundleTransPath = false;
            foreach ($bundleTransPaths as $path) {
                $path .= 'translations';
                if (is_dir($path)) {
                    $bundleTransPath = $path;
                }
            }

            if ($bundleTransPath) {
                $writer->writeTranslations($operation->getResult(), $input->getOption('output-format'), array('path' => $bundleTransPath));
            }
        }
    }
}
