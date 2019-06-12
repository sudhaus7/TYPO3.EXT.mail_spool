.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. _start:


=============
Documentation
=============

This is a Fork of https://github.com/r3h6/TYPO3.EXT.mail_spool

This extension integrates the swiftmailer spool transport for TYPO3 v8 and v9.
The Spool Command has been rewritten to use the Symfony Console, and in 'file' mode the spooled E-Mails are written to the result of Environment::getVarPath()


Installation
------------

.. Through `TER <https://typo3.org/extensions/repository/view/mail_spool/>`_ or with `composer <https://composer.typo3.org/satis.html#!/mail-spool>`_ (typo3-ter/mail-spool).

With ``composer req sudhaus7/mail-spool``

.. warning::
   After installation this extension overwrites in the file "ext_localconf" the mail transport configuration to ``SUDHAUS7\MailSpool\Mail\SpoolTransport``!


Configuration
-------------

You can configure the type of spool and the location where the messsages get stored in the extension configurations.

:spool:
   **file** Messages get stored to the file system till they get sent through the scheduler or cli command.

   **memory** Messages get send at the end of the running process if no error occurs.

   **classname** Custom class which implements the Swift_Spool interface.

   Planned are spoolers for RabbitMQ and Beanstalk

:spool_file_path:
   Path to directory relative to Environment::getVarPath() where the spooled messages get stored. Should not be accessible from outside!

:transport_real:
   Transport used for sending e-mails. Default is same as defined in install tool.

:do_not_spool_syslog_messages:
   Send syslog messages immediatly through mail transport.

Integration
-----------

If you are using the file spool, you must set up an extbase scheduler task or execute the command "spool:send".


Scheduler
---------

There is no direct Scheduler Option anymore, but the process can be scheduled with https://github.com/helhum/typo3-crontab

.. warning::
   The option **daemon** is only for CLI usage.


Commands (CLI)
---------------

See ``./vendor/bin/typo3 mailspool:runspool -h`` for details.

.. note::
   If you like run the command as a daemon on linux systems you can try `Upstart <https://en.wikipedia.org/wiki/Upstart>`_.

.. code-block:: sh

   # Example

   # /etc/init/myscript.conf
   # sudo service myscript start
   # sudo service myscript stop
   # sudo service myscript status

   # Your script information
   description "Send spooled messages."
   author      "R3H6"

   # Describe events for your script
   start on startup
   stop on shutdown

   # Respawn settings
   respawn
   # respawn limit COUNT INTERVAL
   respawn limit unlimited

   # Run your script!
   script
   /var/www/dev7.local.typo3.org/typo3/cli_dispatch.phpsh extbase spool:send --daemon >/dev/null 2>&1
   end script


FAQ
---

After installing, no e-mails get send anymore, why?
   Please read the section "Integration".



Contributing
------------

Bug reports and pull request are welcome through `GitHub <https://github.com/sudhaus7/TYPO3.EXT.mail_spool/>`_.
