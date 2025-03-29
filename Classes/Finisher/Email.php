<?php

namespace Rfuehricht\Formhandler\Finisher;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Finisher to send emails after successful form submission.
 *
 *

 */
class Email extends AbstractFinisher
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

        $mailSettings = $this->settings;

        $templateFile = $this->settings['templateFile'] ?? '';
        if (!$templateFile) {
            return;
        }

        $email = new FluidEmail();

        $email->setTemplate($templateFile);


        //set e-mail options
        $email->subject($mailSettings['subject'] ?? '');

        if (isset($mailSettings['sender']['email'])) {
            $email->sender($this->getAddress($mailSettings['sender']));
        }

        $email->replyTo(...$this->getAddresses($mailSettings['replyTo'] ?? []));
        $email->cc(...$this->getAddresses($mailSettings['cc'] ?? []));
        $email->bcc(...$this->getAddresses($mailSettings['bcc'] ?? []));

        if (isset($mailSettings['returnPath']['email'])) {
            $email->returnPath($this->getAddress($mailSettings['returnPath']));
        }

        $attachments = $mailSettings['attachments'] ?? $mailSettings['attachment'] ?? [];
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
                    $file = rtrim(Environment::getProjectPath() . '/') . '/' . ltrim($attachment, '/');
                    if (file_exists($file)) {
                        $email->attachFromPath($file);
                    }
                }
            }
        }

        $email->assignMultiple([
            'values' => $this->gp
        ]);

        $email->to(...$this->getAddresses($mailSettings['to'] ?? []));

        GeneralUtility::makeInstance(MailerInterface::class)->send($email);

    }

    /**
     * Reads email setting and replaces with values from GET/POST data if available.
     *
     * @param array $settings
     * @return Address
     */
    protected function getAddress(array $settings): Address
    {
        $email = $this->gp[$settings['email']] ?? $settings['email'];
        $name = '';
        if (isset($settings['name'])) {
            $name = $this->gp[$settings['name']] ?? $settings['name'];
        }
        return new Address($email, $name);
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


}
