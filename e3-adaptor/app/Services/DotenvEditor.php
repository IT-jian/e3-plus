<?php


namespace App\Services;


use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Str;

/**
 * .env 文件编辑
 *
 * Class DotenvEditor
 * @package App\Services
 *
 * @author linqihai
 * @since 2019/12/27 13:49
 */
class DotenvEditor
{
    private $env;
    private $backupPath;
    private $autoBackup = false;

    /**
     * DotenvEditor constructor
     */
    public function __construct()
    {
        $backupPath = resource_path('backups/dotenv-editor/');
        $env = base_path('.env');
        $filePermissions = env('FILE_PERMISSIONS', 0755);
        if (!file_exists($env)) {
            return false;
        }
        $this->env = $env;
        if (!is_dir($backupPath)) {
            mkdir($backupPath, $filePermissions, true);
        }
        $this->backupPath = $backupPath;
    }

    /**
     * Returns the value matching to a given key.
     * Returns false, if key does not exist.
     *
     * @param string $key [key to get value]
     *
     * @return mixed
     * @throws ValidationException
     */
    public function getValue($key)
    {
        if ($this->keyExists($key)) {
            return env($key);
        } else {
            throw new ValidationException(trans('dotenv-editor::class.requested_value_not_available'), 0);
        }
    }

    /**
     * Checks, if a given key exists in your .env-file.
     * Returns false or true
     *
     * @param string $key [key]
     *
     * @return bool
     */
    public function keyExists($key)
    {
        $env = $this->getContent();

        return (array_key_exists($key, $env));
    }

    /**
     * Returns the content of a given backup file
     * or the content of the current env file.
     *
     * @param null $timestamp timestamp
     *
     * @return array
     */
    public function getContent($timestamp = null)
    {
        if ($timestamp === null) {
            return $this->envToArray($this->env);
        } else {
            return $this->envToArray($this->getBackupFile($timestamp));
        }
    }

    /**
     * Writes the content of a env file to an array.
     *
     * @param string $file file
     *
     * @return array
     */
    protected function envToArray($file)
    {
        $string = file_get_contents($file);
        $string = preg_split('/\n+/', $string);
        $returnArray = array();
        foreach ($string as $one) {
            if (preg_match('/^(#\s)/', $one) === 1 || preg_match('/^([\\n\\r]+)/', $one)) {
                continue;
            }
            $entry = explode("=", $one, 2);
            $returnArray[$entry[0]] = isset($entry[1]) ? $entry[1] : null;
        }

        return array_filter(
            $returnArray,
            function ($key) {
                return !empty($key);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Returns filename and path for the given timestamp
     *
     * @param null $timestamp timestamp
     *
     * @return string
     * @throws ValidationException
     */
    public function getBackupFile($timestamp)
    {
        if (file_exists($this->getBackupPath() . $timestamp . "_env")) {
            return $this->backupPath . $timestamp . "_env";
        }
        throw new ValidationException(trans('dotenv-editor::class.requested_backup_not_found'), 0);
    }

    /**
     * Returns the current backup-path
     *
     * @return mixed
     */
    public function getBackupPath()
    {
        return $this->backupPath;
    }

    /**
     * Set a new backup-path.
     * The new directory will be created if it doesn't exist
     *
     * @param string $path [backup path]
     *
     * @return bool
     */
    public function setBackupPath($path)
    {
        if (!is_dir($path)) {
            try {
                mkdir($path, 0777, true);
            } catch (InvalidPathException $e) {
                echo htmlspecialchars($e->getMessage());

                return false;
            }
        }
        $this->backupPath = $path;

        return true;
    }

    /**
     * Activate or deactivate the AutoBackup-Functionality
     *
     * @param boolean $onOff [set auto backup on or of]
     *
     * @return void
     * @throws ValidationException
     */
    public function setAutoBackup($onOff)
    {
        if (is_bool($onOff)) {
            $this->autoBackup = $onOff;
        } else {
            throw new ValidationException(trans('dotenv-editor::class.autobackup_wrong_value'), 0);
        }
    }

    /**
     * Restores the latest backup or a backup from a given timestamp.
     * Restores the latest version when no timestamp is given.
     *
     * @param null $timestamp timestamp
     *
     * @return string
     */
    public function restoreBackup($timestamp = null)
    {
        $file = null;
        if ($timestamp !== null) {
            if ($this->getFile($timestamp)) {
                $file = $this->getFile($timestamp);
            }
        } else {
            $file = $this->getFile($this->getLatestBackup()['unformatted']);
        }

        return copy($file, $this->env);
    }

    /**
     * Returns the file for the given backup-timestamp
     *
     * @param null $timestamp timestamp
     *
     * @return string
     * @throws ValidationException
     */
    protected function getFile($timestamp)
    {
        $file = $this->getBackupPath() . $timestamp . "_env";
        if (file_exists($file)) {
            return $file;
        } else {
            throw new ValidationException(trans('dotenv-editor::class.requested_backup_not_found'), 0);
        }
    }

    /**
     * Returns the timestamp of the latest version.
     *
     * @return int|mixed
     */
    protected function getLatestBackup()
    {
        $backups = $this->getBackupVersions();
        $latestBackup = 0;
        foreach ($backups as $backup) {
            if ($backup > $latestBackup) {
                $latestBackup = $backup;
            }
        }

        return $latestBackup;
    }

    /**
     * Returns an array with all available backups.
     * Array contains the formatted and unformatted version of each backup.
     * Throws exception, if no backups were found.
     *
     * @return array
     * @throws ValidationException
     */
    public function getBackupVersions()
    {
        $versions = array_diff(scandir($this->backupPath), array('..', '.'));
        if (count($versions) > 0) {
            $output = array();
            $count = 0;
            foreach ($versions as $version) {
                $part = explode("_", $version);
                $output[$count]['formatted'] = date("Y-m-d H:i:s", (int)$part[0]);
                $output[$count]['unformatted'] = $part[0];
                $count++;
            }

            return $output;
        }
        throw new ValidationException(trans('dotenv-editor::class.no_backups_available'), 0);
    }

    /**
     * Delete the given backup-file
     *
     * @param null $timestamp timestamp
     *
     * @return void
     * @throws ValidationException
     */
    public function deleteBackup($timestamp)
    {
        $file = $this->getBackupPath() . $timestamp . "_env";
        if (file_exists($file)) {
            unlink($file);
        } else {
            throw new ValidationException(trans('dotenv-editor::class.backup_not_deletable'), 0);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Get the content
    |--------------------------------------------------------------------------
    |
    | getContent($backup = NULL)
    |
     */

    /**
     * Returns the given .env as JSON array containing all entries as object
     * with key and value
     *
     * @param null $timestamp timestamp
     *
     * @return string
     */
    public function getAsJson($timestamp = null)
    {
        return $this->envToJson($this->getContent($timestamp));
    }

    /**
     * Converts the given .env-array to JSON
     *
     * @param array $file file
     *
     * @return string
     */
    private function envToJson($file = [])
    {
        $array = [];
        $c = 0;
        foreach ($file as $key => $value) {
            $array[$c] = new \stdClass();
            $array[$c]->key = $key;
            $array[$c]->value = $value;
            $c++;
        }

        return json_encode($array);
    }

    /**
     * Change the given values of the current env file.
     * If the given key(s) is/are not found, nothing happens.
     *
     * @param array $data data
     *
     * @return bool
     * @throws ValidationException
     */
    public function changeEnv($data = array())
    {
        if (count($data) > 0) {
            if ($this->autoBackupEnabled()) {
                $this->createBackup();
            }
            $env = $this->getContent();
            foreach ($data as $dataKey => $dataValue) {
                foreach (array_keys($env) as $envKey) {
                    if ($dataKey === $envKey) {
                        $env[$envKey] = $dataValue;
                    }
                }
            }

            return $this->save($env);
        }
        throw new ValidationException(trans('dotenv-editor::class.array_needed'), 0);
    }

    /**
     * Checks, if Autobackup is enabled
     *
     * @return bool
     */
    public function autoBackupEnabled()
    {
        return $this->autoBackup;
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @return bool
     */
    public function createBackup()
    {
        return copy(
            $this->env,
            $this->backupPath . time() . "_env"
        );
    }

    /**
     * Saves the given data to the .env-file
     *
     * @param array $array array
     *
     * @return bool
     */
    protected function save($array)
    {
        if (is_array($array)) {
            $newArray = array();
            $c = 0;
            foreach ($array as $key => $value) {
                if (preg_match('/\s/', $value) > 0 && (strpos($value, '"') > 0 && strpos($value, '"', -0) > 0)) {
                    $value = '"' . $value . '"';
                }
                $newArray[$c] = $key . "=" . $value;
                $c++;
            }
            $newArray = implode("\n", $newArray);
            file_put_contents($this->env, $newArray);

            return true;
        }

        return false;
    }

    /**
     * Add data to the current env file.
     * Data will be placed at the end.
     *
     * @param array $data data
     *
     * @return bool
     * @throws ValidationException
     */
    public function addData($data = array())
    {
        if (count($data) > 0) {
            if ($this->autoBackupEnabled()) {
                $this->createBackup();
            }
            $env = $this->getContent();
            // Expand the existing array with the new data
            foreach ($data as $key => $value) {
                $env[$key] = $this->santize($value);
            }

            return $this->save($env);
        }
        throw new ValidationException(trans('dotenv-editor::class.array_needed'), 0);
    }

    /**
     * [santize description]
     *
     * @param string $value [description]
     *
     * @return $value        santize value
     */
    public function santize($value = '')
    {
        if ($this->isStartOrEndWith($value, '\'')) {
            $value = $this->setStartAndEndWith($value, '\'');
        }
        if ($this->isStartOrEndWith($value, '"')) {
            $value = $this->setStartAndEndWith($value, '"');
        }
        if (preg_match('/\s/', $value)) {
            $value = $this->setStartAndEndWith($value, '"');
        }

        return $value;
    }

    /**
     * [isStartOrEndWith description]
     *
     * @param string $value value
     * @param string $string check string
     *
     * @return boolean
     */
    public function isStartOrEndWith($value, $string = '')
    {
        return Str::startsWith($value, $string) || Str::endsWith($value, $string);
    }

    /**
     * [setStartAndEndWith description]
     *
     * @param string $value value
     * @param string $string check string
     *
     * @return string value
     */
    public function setStartAndEndWith($value, $string = '')
    {
        $value = Str::start($value, '"');
        $value = Str::finish($value, '"');

        return $value;
    }

    /**
     * Delete one or more entries from the env file.
     *
     * @param array $data data
     *
     * @return bool
     * @throws ValidationException
     */
    public function deleteData($data = array())
    {
        foreach ($data as $key => $value) {
            if (!is_numeric($key)) {
                throw new ValidationException("dotenv-editor::class.numeric_array_needed", 0);
            }
        }
        if ($this->autoBackupEnabled()) {
            $this->createBackup();
        }
        $env = $this->getContent();
        foreach ($data as $delete) {
            foreach ($env as $key => $value) {
                if ($delete === $key) {
                    unset($env[$key]);
                }
            }
        }

        return $this->save($env);
    }
}