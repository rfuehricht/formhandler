<?php

namespace Rfuehricht\Formhandler\Component;


use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This finisher stores the submitted values into a table in the TYPO3 database according to the configuration
 *
 * Example configuration:
 *
 * <code>
 * finishers.1.class = DbSave
 *
 * #The table to store the records in
 * finishers.1.config.table = tt_content
 *
 * #The uid field. Default: uid
 * finishers.1.config.key = uid
 *
 * #Do not insert the record, but update an existing one.
 * #The uid of the existing record must exist in Get/Post
 * finishers.1.config.updateInsteadOfInsert = 1
 *
 * #map a form field to a db field.
 * finishers.1.config.fields.header.mapping = name
 *
 * # if form field is empty, insert configured content instead
 * finishers.1.config.fields.header.ifIsEmpty = None given
 * finishers.1.config.fields.bodytext.mapping = interests
 *
 * #if form field is an array, implode using this separator. Default: ,
 * finishers.1.config.fields.bodytext.separator = ,
 *
 * #add static values for some fields
 * finishers.1.config.fields.hidden = 1
 * finishers.1.config.fields.pid = 39
 *
 * #add special values
 * finishers.1.config.fields.subheader.special = sub_datetime
 * finishers.1.config.fields.crdate.special = sub_tstamp
 * finishers.1.config.fields.tstamp.special = sub_tstamp
 * finishers.1.config.fields.imagecaption.special = ip
 * </code>
 *

 */
class DbSave extends AbstractComponent
{

    /**
     * The name of the table to put the values into.
     *
     * @access protected
     * @var string
     */
    protected string $table = '';

    /**
     * The field in the table holding the primary key.
     *
     * @access protected
     * @var string
     */
    protected string $key = '';

    /**
     * A flag to indicate if to insert the record or to update an existing one
     *
     * @access protected
     * @var bool
     */
    protected bool $doUpdate = false;

    /**
     * @access protected
     * @var QueryBuilder
     */
    protected QueryBuilder $queryBuilder;

    public function process(): array|ResponseInterface
    {
        $this->table = $this->settings['table'];

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->table);

        //set primary key field
        $this->key = $this->settings['key'] ?? 'uid';

        //check whether to update or to insert a record
        $this->doUpdate = false;
        if (intval($this->settings['updateInsteadOfInsert'] ?? 0) === 1) {
            $this->doUpdate = $this->doesRecordExist($this->getUpdateUid());
        }


        //set fields to insert/update
        $queryFields = $this->parseFields();

        //query the database
        $isSuccess = $this->save($queryFields);

        if (!isset($this->gp['saveDB'])) {
            $this->gp['saveDB'] = [];
        }

        //Store info in GP only if the query was successful
        if ($isSuccess) {
            //Get DB info, including UID
            if (!$this->doUpdate) {
                $this->gp['inserted_uid'] = $this->getInsertedUid();
                $this->gp[$this->table . '_inserted_uid'] = $this->gp['inserted_uid'];
                $info = [
                    'table' => $this->table,
                    'uid' => $this->gp['inserted_uid'],
                    'uidField' => $this->key
                ];
            } else {
                $uid = $this->getUpdateUid();
                $info = [
                    'table' => $this->table,
                    'uid' => $uid,
                    'uidField' => $this->key
                ];
            }
            $this->gp['saveDB'][] = $info;

            //Insert the data written to DB into GP array
            $dataKeyName = $this->table;
            $dataKeyIndex = 1;
            while (isset($this->gp['saveDB'][$dataKeyName])) {
                $dataKeyIndex++;
                $dataKeyName = $this->table . '_' . $dataKeyIndex;
            }
            $this->gp['saveDB'][$dataKeyName] = $queryFields;
        }
        return $this->gp;
    }

    protected function doesRecordExist(int $uid): bool
    {
        $exists = false;
        if ($uid) {
            $count = $this->queryBuilder
                ->count($this->key)
                ->from($this->table)
                ->where($this->queryBuilder->expr()->eq($this->key, $uid))
                ->executeQuery()
                ->fetchFirstColumn();
            if ($count > 0) {
                $exists = true;
            }
        }
        return $exists;
    }

    /**
     * Returns current UID to use for updating the DB.
     *
     * @return int uid
     */
    protected function getUpdateUid(): int
    {
        return $this->settings['keyValue'] ?? $this->gp[$this->key] ?? 0;
    }

    /**
     * Parses mapping settings and builds an array holding the query fields information.
     *
     * @return array The query fields
     */
    protected function parseFields(): array
    {
        $queryFields = [];

        //parse mapping
        foreach ($this->settings['fields'] as $fieldName => $options) {
            $fieldValue = '';
            if (isset($options) && is_array($options)) {
                if (!isset($options['special'])) {
                    $mapping = $options['mapping'] ?? $fieldName;
                    if (isset($mapping['_typoScriptNodeValue'])) {
                        /** @var ContentObjectRenderer $contentObjectRenderer */
                        $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
                        $mapping = $contentObjectRenderer->cObjGetSingle($mapping['_typoScriptNodeValue'], $mapping);
                    }
                    $fieldValue = $this->gp[$mapping] ?? '';


                    //preprocess the field value. e.g. to format a date
                    /*if (isset($options['preProcessing']) && is_array($options['preProcessing'])) {
                        if (!isset($options['preProcessing']['value'])) {
                            $options['preProcessing']['value'] = $fieldValue;
                        }
                        $fieldValue = $this->utilityFuncs->getSingle($options, 'preProcessing');
                    }

                    if (isset($options['mapping.']) && is_array($options['mapping.'])) {
                        if (!isset($options['mapping.']['value'])) {
                            $options['mapping.']['value'] = $fieldValue;
                        }
                        $fieldValue = $this->utilityFuncs->getSingle($options, 'mapping');
                    }*/

                    //process empty value handling
                    if (isset($options['ifIsEmpty']) && strlen($fieldValue) === 0) {
                        $fieldValue = $options['ifIsEmpty'];
                    }

                    if (intval($options['zeroIfEmpty'] ?? 0) === 1 && strlen($fieldValue) === 0) {
                        $fieldValue = 0;
                    }

                    //process array handling
                    if (is_array($fieldValue)) {
                        $separator = ',';
                        if ($options['separator']) {
                            $separator = $options['separator'];
                        }
                        $fieldValue = implode($separator, $fieldValue);
                    }

                    //process uploaded files
                    $files = $this->globals->getSession()->get('files');
                    if (isset($files[$fieldName]) && is_array($files[$fieldName])) {
                        $fieldValue = $this->getFileList($files[$fieldName]);
                    }
                } else {
                    switch ($options['special']) {
                        case 'files':
                            $field = $options['special']['field'] ?? '';
                            $separator = $options['special']['separator'] ?? '';

                            $filesArray = [];
                            if (intval($options['special']['useFal'] ?? 0) === 1) {

                                //Remove existing references
                                $existingRecordUid = $this->getUpdateUid();
                                if ($existingRecordUid) {
                                    $queryBuilder = GeneralUtility::makeInstance(
                                        ConnectionPool::class
                                    )->getQueryBuilderForTable('sys_file_reference');
                                    $queryBuilder
                                        ->delete('sys_file_reference')
                                        ->where('tablenames="' . $this->table . '" AND fieldname="' . $fieldName . '" AND uid_foreign=' . $existingRecordUid)
                                        ->executeStatement();
                                }
                                $files = $this->globals->getSession()->get('files');

                                $count = 0;
                                if (isset($files[$field]) && is_array($files[$field])) {
                                    foreach ($files[$field] as $file) {
                                        $fileId = $file['falId'];
                                        if ($fileId) {
                                            $queryBuilder = GeneralUtility::makeInstance(
                                                ConnectionPool::class
                                            )->getQueryBuilderForTable('sys_file_reference');
                                            $queryBuilder
                                                ->insert('sys_file_reference')
                                                ->values([
                                                    'uid_local' => $fileId,
                                                    'uid_foreign' => $existingRecordUid,
                                                    'tablenames' => $this->table,
                                                    'fieldname' => $fieldName
                                                ])
                                                ->executeStatement();

                                            $count++;
                                        }
                                    }
                                }
                                $fieldValue = $count;
                            } else {
                                $info = $options['special']['info'] ?? '[uploaded_name]';

                                $files = $this->globals->getSession()->get('files');
                                if (isset($files[$field]) && is_array($files[$field])) {
                                    foreach ($files[$field] as $file) {
                                        $infoString = $info;
                                        foreach ($file as $infoKey => $infoValue) {
                                            $infoString = str_replace('[' . $infoKey . ']', $infoValue, $infoString);
                                        }
                                        $filesArray[] = $infoString;
                                    }
                                }
                                if (isset($options['special']['index'])) {
                                    $index = $options['special']['index'];
                                    if (isset($filesArray[$index])) {
                                        $fieldValue = $filesArray[$index];
                                    }
                                } else {
                                    $fieldValue = implode($separator, $filesArray);
                                }
                            }
                            break;
                        case 'datetime':
                            $field = $options['special']['field'] ?? '';
                            if (isset($this->gp[$field])) {
                                $date = $this->gp[$field];
                                $dateFormat = $options['special']['format'] ?? 'Y-m-d H:i:s';

                                $fieldValue = $this->formUtility->dateToTimestamp($date, $dateFormat);
                            } else {
                                $fieldValue = time();
                            }
                            break;
                        case 'sub_datetime':
                            $dateFormat = $options['special']['format'] ?? 'Y-m-d H:i:s';
                            $fieldValue = date($dateFormat, time());
                            break;
                        case 'sub_tstamp':
                            $fieldValue = time();
                            break;
                        case 'ip':
                            $ip = $_SERVER['REMOTE_ADDR'];

                            if (isset($_SERVER['HTTP_X_REAL_IP'])) {
                                $ip = $_SERVER['HTTP_X_REAL_IP'];
                            }
                            if (isset($options['special']['customProperty']) && isset($_SERVER[$options['special']['customProperty']])) {
                                $ip = $_SERVER[$options['special']['customProperty']];
                            }
                            $fieldValue = $ip;
                            break;
                        case 'inserted_uid':
                            $table = $options['special']['table'] ?? '';
                            if (isset($this->gp['saveDB']) && is_array($this->gp['saveDB'])) {
                                foreach ($this->gp['saveDB'] as $info) {
                                    if ($info['table'] === $table) {
                                        $fieldValue = $info['uid'];
                                    }
                                }
                            }
                            break;
                    }
                }
            } else {
                $fieldValue = $options;
            }

            //post process the field value after formhandler did it's magic.
            /*if (isset($options['postProcessing']) && is_array($options['postProcessing'])) {
                if (!isset($options['postProcessing']['value'])) {
                    $options['postProcessing']['value'] = $fieldValue;
                }
                $fieldValue = $options['postProcessing'];
            }*/

            $queryFields[$fieldName] = $fieldValue;

            if (intval($options['nullIfEmpty'] ?? 0) === 1 && strlen($fieldValue) == 0) {
                unset($queryFields[$fieldName]);
            }
        }
        return $queryFields;
    }

    /**
     * Returns a list of uploaded files from given field.
     *
     * @param array $files
     * @return string list of filenames
     */
    protected function getFileList(array $files): string
    {
        $filenames = [];
        foreach ($files as $file) {
            $filenames[] = $file['uploaded_name'];
        }
        return implode(',', $filenames);
    }

    /**
     * Method to query the database making an insert or update statement using the given fields.
     *
     * @param array $queryFields Array holding the query fields
     * @return boolean Success flag
     */
    protected function save(array $queryFields): bool
    {

        //insert
        if (!$this->doUpdate) {
            $isSuccess = $this->doInsert($queryFields);
        } //update
        else {
            //check if uid of record to update is in GP
            $uid = $this->getUpdateUid();

            $isSuccess = $this->doUpdate($uid, $queryFields);
        }

        return $isSuccess;
    }

    protected function doInsert(array $queryFields): bool
    {

        $affectedRows = $this->queryBuilder
            ->insert($this->table)
            ->values($queryFields)
            ->executeStatement();
        return ($affectedRows > 0);
    }

    protected function doUpdate(int $uid, array $queryFields): bool
    {

        $query = $this->queryBuilder->update($this->table);
        foreach ($queryFields as $field => $value) {
            $query->set($field, $value);
        }
        $uid = $this->queryBuilder->quote($uid);
        $query->where(
            $this->queryBuilder->expr()->eq($this->key, $uid)
        );
        $affectedRows = $query->executeStatement();
        return ($affectedRows > 0);
    }

    /**
     * Returns the last inserted uid
     *
     * @return int uid
     */
    protected function getInsertedUid(): int
    {
        $uid = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(
            $this->table
        )->lastInsertId();
        return intval($uid);
    }

}
