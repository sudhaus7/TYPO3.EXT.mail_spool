<?php

namespace SUDHAUS7\MailSpool\Mail;

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

use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * SpoolTransport.
 *
 * Wrapper class for using configured spool for transport.
 */
class SpoolTransport extends \Swift_SpoolTransport
{
    /**
     * Mail configuration.
     *
     * @var array
     */
    protected $configuration;

    /**
     * Constructor.
     *
     * @param array $configuration System mail configuration.
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
        $spool = $this->getSpoolFactory()->get($configuration);
        parent::__construct($spool);
    }

    
    /**
     * Get real transport for sending messages.
     *
     * @return \Swift_Transport
     * @throws \Exception
     */
    public function getRealTransport()
    {
        try {
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = $this->configuration['transport_real'];
            $mailer = $this->getMailer();
            $transport = $mailer->getTransport();
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = $this->configuration['transport'];

            return $transport;
        } catch (\Exception $exception) {
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = $this->configuration['transport'];
            throw new \Exception('Could not create real transport '.$exception->getMessage(), 1476212381);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        
        if ($message->getSubject() === 'Warning - error in TYPO3 installation' && isset($this->configuration['do_not_spool_syslog_messages']) && true === (bool) $this->configuration['do_not_spool_syslog_messages']) {
            return $this->getMailTransport()->send($message, $failedRecipients);
        }

        return parent::send($message, $failedRecipients);
    }

    /**
     * Returns swift mailer mail transport.
     *
     * @return MailTransport
     */
    protected function getMailTransport()
    {
        return \Swift_MailTransport::newInstance();
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

    /**
     * [getSpoolFactory description].
     *
     * @return \SUDHAUS7\MailSpool\Mail\SpoolFactory
     */
    protected function getSpoolFactory()
    {
        return GeneralUtility::makeInstance(SpoolFactory::class);
    }
}
