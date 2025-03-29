<?php

namespace Rfuehricht\Formhandler\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This finisher generates a unique code for a database entry.
 * This can be used for FE user registration or newsletter registration.
 *

 */
class GenerateAuthCode extends AbstractFinisher
{
    /**
     * The main method called by the controller
     *
     */
    public function process(): array|ResponseInterface
    {
        $firstInsertInfo = [];

        $uidField = $this->settings['uidField'] ?? 'uid';
        if (isset($this->settings['uid'])) {
            $firstInsertInfo = [
                'table' => $this->settings['table'] ?? '',
                'uidField' => $uidField,
                'uid' => $this->settings['uid']
            ];
        } elseif (isset($this->gp['saveDB'])) {
            if (isset($this->settings['table'])) {
                $table = $this->settings['table'] ?? '';

                foreach ($this->gp['saveDB'] as $insertInfo) {
                    if ($insertInfo['table'] === $table) {
                        $firstInsertInfo = $insertInfo;
                        break;
                    }
                }
            }
            if (empty($firstInsertInfo)) {
                $firstInsertInfo = reset($this->gp['saveDB']);
            }
        }
        $table = $firstInsertInfo['table'];

        $uid = $firstInsertInfo[$uidField];

        if ($table && $uid && $uidField) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);

            $selectFields = ['*'];
            if (isset($this->settings['selectFields'])) {
                $selectFields = GeneralUtility::trimExplode(',', $this->settings['selectFields']);
            }

            $queryBuilder->getRestrictions()
                ->removeByType(HiddenRestriction::class);
            $query = $queryBuilder->select(...$selectFields)
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq($uidField, $queryBuilder->createNamedParameter($uid))
                );

            $row = $query
                ->executeQuery()
                ->fetchAssociative();

            if ($row) {
                $authCode = self::generateAuthCode($row);

                $pageArguments = $this->request->getAttribute('routing');
                $pageId = $pageArguments->getPageId();
                $authCodePage = $this->settings['authCodePage'] ?? $pageId;

                //create the parameter-array for the authCode Link
                $paramsArray = array_merge($firstInsertInfo, ['authCode' => $authCode]);

                if ($this->settings['excludeParams']) {
                    $excludeParams = GeneralUtility::trimExplode(',', $this->settings['excludeParams']);
                    foreach ($excludeParams as $param) {
                        if (isset($paramsArray[$param])) {
                            unset($paramsArray[$param]);
                        }
                    }
                }

                $formValuesPrefix = $this->globals->getFormValuesPrefix();

                if (!empty($formValuesPrefix)) {
                    $paramsArray = [$formValuesPrefix => $paramsArray];
                }

                $linkConf = [
                    'parameter' => $authCodePage,
                    'additionalParams' => GeneralUtility::implodeArrayForUrl('', $paramsArray),
                    'forceAbsoluteUrl' => 1
                ];


                /** @var ContentObjectRenderer $contentObjectRenderer */
                $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
                $url = $contentObjectRenderer->typoLink_URL($linkConf);
                $this->gp['authCode'] = $authCode;
                $this->gp['authCodeUrl'] = $url;
            }
        }
        return $this->gp;
    }


    /**
     * Return a hash value to send by email as an auth code.
     *
     * @param array The submitted form data
     * @return string The auth code
     */
    public function generateAuthCode(array $row): string
    {
        /** @var HashService $hashService */
        $hashService = GeneralUtility::makeInstance(HashService::class);
        return $hashService->hmac(serialize($row), 'formhandler');
    }
}
