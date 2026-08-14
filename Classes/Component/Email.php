<?php

namespace Rfuehricht\Formhandler\Component;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Finisher to send emails after successful form submission.
 */
class Email extends AbstractComponent
{

    /**
     * The main method called by the controller
     *
     * @return array|ResponseInterface The probably modified GET/POST parameters
     */
    public function process(): array|ResponseInterface
    {
        $this->sendMail();

        return $this->gp;
    }

    /**
     * Sends mail.
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    protected function sendMail(): void
    {
        $templateFile = $this->settings['templateFile'] ?? '';
        if (!$templateFile) {
            return;
        }

        $email = new FluidEmail();

        $email->setTemplate($templateFile);

        //set e-mail options
        $email->subject($this->settings['subject'] ?? '');

        $singleAddressFields = ['sender', 'returnPath'];
        $multipleAddressFields = ['replyTo', 'to', 'cc', 'bcc'];

        foreach ($singleAddressFields as $singleAddressField) {
            $email = $this->setSingleAddress($email, $singleAddressField);
        }
        foreach ($multipleAddressFields as $multipleAddressField) {
            $email = $this->setAddresses($email, $multipleAddressField);
        }

        $email = $this->addAttachments($email);
        $email = $this->embedFiles($email);

        $email->assignMultiple([
            'values' => $this->gp
        ]);

        GeneralUtility::makeInstance(MailerInterface::class)->send($email);
    }

    /**
     * Sets a single address field.
     */
    protected function setSingleAddress(FluidEmail $email, string $field): FluidEmail
    {
        if (isset($this->settings[$field])) {
            if (is_string($this->settings[$field])) {
                $email->$field(new Address($this->settings[$field]));
            } elseif (isset($this->settings[$field]['email'])) {
                $email->$field($this->getAddress($this->settings[$field]));
            }
        }
        return $email;
    }

    /**
     * Reads email setting and replaces with values from GET/POST data if available.
     *
     * @param array|string $settings
     * @return Address
     */
    protected function getAddress(array|string $settings): Address
    {
        if (is_string($settings)) {
            $settings = ['email' => $settings];
        }
        $email = $this->gp[$settings['email']] ?? $settings['email'];
        $name = '';
        if (isset($settings['name'])) {
            $name = $this->gp[$settings['name']] ?? $settings['name'];
        }
        return new Address($email, $name);
    }

    /**
     * Sets multiple addresses.
     */
    protected function setAddresses(FluidEmail $email, string $field): FluidEmail
    {
        if (isset($this->settings[$field])) {
            // allow single email address or multiple in TypoScript
            if (is_string($this->settings[$field])) {
                $email->$field($this->getAddress($this->settings[$field]));
            } elseif (isset($this->settings[$field]['email'])) {
                $email->$field($this->getAddress($this->settings[$field]));
            } else {
                $email->$field(...$this->getAddresses($this->settings[$field] ?? []));
            }
        }
        return $email;
    }

    /**
     * Read settings and return array of Address objects to use with email object
     *
     * @param array $settings
     * @return array
     */
    protected function getAddresses(array $settings): array
    {
        $addresses = [];
        foreach ($settings as $setting) {
            $addresses[] = $this->getAddress($setting);
        }
        return $addresses;
    }

    protected function addAttachments(FluidEmail $email): FluidEmail
    {
        $attachments = $this->settings['attachments'] ?? $this->settings['attachment'] ?? [];
        if (!is_array($attachments)) {
            $attachments = GeneralUtility::trimExplode(',', $attachments);
        }
        foreach ($attachments as $attachment) {
            if (strlen($attachment) > 0) {
                $sessionFiles = $this->globals->getSession()->get('files');

                if (isset($sessionFiles[$attachment])) {
                    foreach ($sessionFiles[$attachment] as $fileInfo) {
                        $email->attachFromPath($fileInfo['uploaded_path'] . $fileInfo['uploaded_name']);
                    }
                } else {
                    $path = $this->processTypoScriptValue($attachment);
                    $path = GeneralUtility::getFileAbsFileName($path);
                    if (file_exists($path)) {
                        $email->attachFromPath($path);
                    }
                }
            }
        }
        return $email;
    }

    protected function embedFiles(FluidEmail $email): FluidEmail
    {
        $filesToEmbed = $this->settings['embed'] ?? [];

        // Only single entry
        if (isset($filesToEmbed['path'])) {
            $filesToEmbed = [$filesToEmbed];
        }
        foreach ($filesToEmbed as $key => $fileToEmbed) {
            if (isset($fileToEmbed['path']) && isset($fileToEmbed['name'])) {
                $name = $fileToEmbed['name'];
                $path = $fileToEmbed['path'];
            } else {
                $name = $key;
                $path = $fileToEmbed['path'] ?? $fileToEmbed;
            }
            if (!$name || !$path) {
                throw new Exception('Unable to embed file. Path or name not set.');
            }
            $path = $this->processTypoScriptValue($path);
            $path = GeneralUtility::getFileAbsFileName($path);
            if (file_exists($path)) {
                $email->embedFromPath($path, $name);
            }
        }
        return $email;
    }


}
