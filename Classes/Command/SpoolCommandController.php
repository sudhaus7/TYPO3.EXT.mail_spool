<?php

namespace SUDHAUS7\MailSpool\Command;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 3 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * SpoolCommandController.
 *
 * @link https://github.com/symfony/swiftmailer-bundle/blob/master/Command/SendEmailCommand.php
 */
class SpoolCommandController extends Command
{
    
    /**
     *
     */
    public function configure() {
        
        $this->setDescription('Sends emails from the spool');
        $this->setHelp('');
        $this->addOption('messageLimit','m',InputOption::VALUE_OPTIONAL,'The maximum number of messages to send',0)
            ->addOption('timeLimit','t',InputOption::VALUE_OPTIONAL,'The time limit for sending messages (in seconds)',0)
            ->addOption('recoverTimeout','r',InputOption::VALUE_OPTIONAL,'The timeout for recovering messages that have taken too long to send (in seconds)',null)
            ->addOption('daemon','d',InputOption::VALUE_OPTIONAL,'True for running as daemon (EXPERIMENTAL, CLI ONLY!)',false);
        
    }
    
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
    
        $messageLimit = 0;
        if($input->hasOption('messageLimit')) {
            $messageLimit =  MathUtility::forceIntegerInRange((int)$input->getOption('messageLimit'), 0);
        }
        $timeLimit = 0;
        if($input->hasOption('timeLimit')) {
            $timeLimit =  MathUtility::forceIntegerInRange((int)$input->getOption('timeLimit'), 0);
        }
        $recoverTimeout = null;
        if($input->hasOption('recoverTimeout')) {
            $recoverTimeout = $input->getOption('recoverTimeout');
        }
        $daemon = false;
        if($input->hasOption('daemon')) {
            $daemon = (bool)$input->getOption('daemon');
        }
    
    
        $mailer = $this->getMailer();
        $transport = $mailer->getTransport();
        if ($transport instanceof \SUDHAUS7\MailSpool\Mail\SpoolTransport) {
            $spool = $transport->getSpool();
            if ($spool instanceof \Swift_ConfigurableSpool) {
                $spool->setMessageLimit($messageLimit);
                $spool->setTimeLimit($timeLimit);
            }
            do {
                if ($spool instanceof \Swift_FileSpool) {
                    if (null !== $recoverTimeout) {
                        $spool->recover($recoverTimeout);
                    } else {
                        $spool->recover();
                    }
                }
            
                try {
                    $sent = $spool->flushQueue($transport->getRealTransport());
                } catch (\Exception $exception) {
                    $message = $exception->getMessage();
                    $io->error($message);
                    throw $exception;
                }
                if (!$io->isQuiet()) {
                    $io->note(sprintf('%d emails sent', $sent));
                }
            } while ($daemon && $this->idle());
        } else {
            $io->error('Transport is not a Swift_Transport_SpoolTransport.');
        }
        
    }

    
    /**
     * Be idle for a while.
     *
     * @return bool true if relaxed ;-)
     */
    protected function idle()
    {
        return !sleep(3);
    }

    /**
     * Returns the TYPO3 mailer.
     *
     * @return \TYPO3\CMS\Core\Mail\Mailer
     */
    protected function getMailer()
    {
        return GeneralUtility::makeInstance(Mailer::class);
    }
    
}
