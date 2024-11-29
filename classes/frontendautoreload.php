<?php
/**
 * Visionen & Kreationen – FrontendAutoReload for WBCE CMS
 * https://github.com/digitalbricks/WbceFrontendAutoReload
 *
 * This module is a simple helper for automatically reloading the browser window when a file in the
 * /templates/[current_template]/ directory is changed. This is useful for development purposes.
 */

class FrontendAutoReload extends Wb {


    private string $modulebase = '/modules/frontendautoreload/';
    private string $endpoint = 'latest.php';
    private array $excludedDirectories = ['/images'];
    private array $excludedExtensions = ['jpeg', 'jpg', 'png', 'svg', 'gif'];
    private int $interval = 5;
    private string $watchedDir = '';

    public function __construct()
    {
        parent::__construct();

        // if the constant TEMPLATE is not defined, set it to the default template
        if (!defined('TEMPLATE')) {
            $sTemplate = DEFAULT_TEMPLATE;
            if (isset($this->page['template']) and $this->page['template'] != '') {
                if (file_exists(WB_PATH . '/templates/' . $this->page['template'] . '/index.php')) {
                    $sTemplate = $this->page['template'];
                }
            }
            define('TEMPLATE', $sTemplate);
        }

        $this->watchedDir = WB_PATH. '/templates/' . TEMPLATE;
        $this->endpoint = WB_URL . $this->modulebase . $this->endpoint;

    }

    public function returnResults() {
        if(!$this->isAllowed()) return; // Only add the hook if the user is allowed
        if(!$this->isAdmin()) return; // Only add the hook if the user is an admin

        // get config from POST
        $this->getConfigFromPost();

        header('Content-Type: application/json; charset=utf-8');
        $timestamp = $this->getLatestModificationTime();
        return json_encode($timestamp);


    }


    /**
     * Returns the desired polling interval in seconds
     *
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }


    /**
     * @return array|string[]
     */
    public function getExcludedDirectories(): array
    {
        return $this->excludedDirectories;
    }


    /**
     * @return array|string[]
     */
    public function getExcludedExtensions(): array
    {
        return $this->excludedExtensions;
    }


    /**
     * @param array $excludedDirectories
     * @return void
     */
    public function setExcludedDirectories(array $excludedDirectories): void
    {
        $this->excludedDirectories = $excludedDirectories;
    }


    /**
     * @param array $excludedExtensions
     * @return void
     */
    public function setExcludedExtensions(array $excludedExtensions): void
    {
        $this->excludedExtensions = $excludedExtensions;
    }


    /**
     * @param int $interval
     * @return void
     */
    public function setInterval(int $interval): void
    {
        $this->interval = $interval;
    }


    /**
     * Returns true if these conditions are met:
     * - Debug mode is enabled
     * - User is logged in
     * - User is superuser
     *
     * @return bool
     */
    private function isAllowed(){
        if ($this->is_authenticated() === false) return false;
        return true;
    }





    /**
     * Iterates over watched directory and subdirectories to find the latest modification time
     *
     * @return int timestamp of the latest modification in the watched directory
     */
    private function getLatestModificationTime() {
        $latestTime = 0;
        $directory = $this->watchedDir;

        // Define the image extensions to exclude
        $excludedExtensions = ['jpeg', 'jpg', 'png', 'svg', 'gif'];

        // Create a new FilesystemIterator
        // @source: https://www.php.net/manual/en/class.recursivedirectoryiterator.php#85805
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::SELF_FIRST);


        // Iterate through each file in the directory and subdirectories
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                // Check if the directory is in the excluded list
                $folderRelativePath = $this->getStrippedPath($file->getPath());
                if(in_array($folderRelativePath, $this->excludedDirectories)) {
                    continue;
                }
            }

            // Check if it's a file (not a directory)
            if ($file->isFile()) {
                // Get the file extension
                $fileExtension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));

                // Check if the file extension is in the excluded list
                if(in_array($fileExtension, $this->excludedExtensions)) {
                    continue;
                }

                // Get the modification time of the file
                $fileTime = $file->getMTime();
                // Update the latest modification time if this file is newer
                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                }
            }
        }
        return $latestTime;
    }


    /**
     * Strips the watched directory from the path
     * e.g. /var/www/html/site/templates/images/icons -> /images/icons
     *
     * @param string $path
     * @return string
     */
    private function getStrippedPath(string $path):string {
        $search = rtrim($this->watchedDir,"/");
        return str_replace($search, '', $path);
    }

    /**
     * Renders the frontend script element
     *
     * @return string
     */
    public function renderScript() {
        if(!$this->isAllowed()) return ''; // Only render the script if the user is allowed
        return "\n\n".$this->renderFile('/components/frontend-js.php', ['far' => $this]);
    }

    /**
     * Returns the (relative) URL of the endpoint
     *
     * @return string
     */
    public function getEndpointUrl(): string
    {
        $endpoint = ltrim($this->endpoint,"/");
        return $endpoint;
    }


    /**
     * Returns the configuration as an array
     *
     * @return array
     */
    public function getConfig():array
    {
        return [
            'excludedDirectories' => $this->excludedDirectories,
            'excludedExtensions' => $this->excludedExtensions,
            'interval' => $this->interval
        ];
    }


    /**
     * Sets the configuration from the POST request
     * (JSON encoded)
     *
     * @return void
     */
    private function getConfigFromPost() {
        $post = file_get_contents('php://input');
        if(!$post) return;
        $data = json_decode($post, true);
        if(is_array($data) && array_key_exists('excludedDirectories', $data)) {
            $this->setExcludedDirectories($data['excludedDirectories']);
        }
        if(is_array($data) && array_key_exists('excludedExtensions', $data)) {
            $this->setExcludedExtensions($data['excludedExtensions']);
        }
        if(is_array($data) && array_key_exists('interval', $data)) {
            $this->setInterval($data['interval']);
        }
    }

    private function renderFile(string $filename, array $data = []): string
    {
        $base = WB_PATH . $this->modulebase;
        if (!file_exists($base.$filename)) {
            return 'NOT FOUND: '.$filename;
        }
        ob_start();
        extract($data);
        include $base.$filename;
        return ob_get_clean();
    }
}
