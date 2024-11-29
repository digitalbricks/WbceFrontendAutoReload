# FrontendAutoReload Module for WBCE CMS

This module watches the `/templates/your_template` folder for file changes and triggers a reload in the frontend if changes are detected.


## Requirements
* WBCE >= 1.4.0
* PHP >= 8.2 (not tested lower versions but may work)


## Installation

1. Download the ZIP file from Github
2. Login to your WBCE backend
3. Go to `Settings` -> `Modules` -> `Install module`
4. Upload the ZIP file


## Usage

In order to load the needed javascript you have to add this in your template `index.php`, somewhere before the closing `</body>` tag:

```php
$far = frontendautoreload();
echo $far->renderScript();
```


## Configuration

There are three options you may configure:

* polling interval in seconds (default: `5`) - via `(int) setInterval()`
* excluded direcories (default: `'/images'`) – via `(array) setExcludedDirectories()`
* excluded file extensions (default: `'jpeg', 'jpg', 'png', 'svg', 'gif'`) – via `(array) setExcludedExtensions()` 

Here is an example using all of the mentioned methods:

```php
// get a module instance
$far = frontendautoreload();

// set polling interval to 2 seconds
$far->setInterval(2);

// exclude site/templates/assets and site/templates/vendor
$far->setExcludedDirectories(['/assets', '/vendor']); // note the leading slash!

// exclude markdown and bitmap files
$far->setExcludedExtensions(['md', 'bmp']); // without dot

// output the javascript
echo $far->renderScript();
```

It's important that the configuration is assigned **before** calling `renderScript()` as that output reflects the configuration.

In the default configuration, images (and the folder that can potentially contain images) are intentionally excluded. This is because the module is intended mainly to react to code changes – but you may change this behavior by using the above-mentioned methods.

## Admin only
Please be aware that the module is designed to be only active for logged in users in admin group (be default the initial user who installed WBCE, creating the admin user account). In any other case the URL endpoint won't answer and the script won't be rendered.


## Technical Background
This snippet module provides a file as an HTTP endpoint. This endpoint returns the timestamp of the latest modified file as JSON. A script in the frontend (rendered via `renderScript()`) polls that endpoint in the configured interval and triggers a reload if the timestamp changes (is higher than the previous one).


