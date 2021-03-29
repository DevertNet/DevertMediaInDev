<?php

namespace DevertMediaInDev;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\Router;
use Shopware\Components\Translation as TranslationComponent;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Shopware-Plugin DevertMediaInDev.
 */
class DevertMediaInDev extends Plugin
{
    public $downloadUrl = false;
    
    /**
     * subscribe on events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Legacy_Struct_Converter_Convert_Media' => 'convertMedia',
        ];
    }

    public function convertMedia(\Enlight_Event_EventArgs $arguments)
    {
        $media = $arguments->getMedia();

        //check if download url is configured
        if (!$this->getDownloadUrl()) {
            return;
        }

        try {
            $this->checkFile($media->getPath());
    
            foreach ($media->getThumbnails() as $thumbnail) {
                $this->checkUrl($thumbnail->getSource());
                $this->checkUrl($thumbnail->getRetinaSource());
            }
        } catch (\Exception $e) {
            $message = sprintf(
                'DevertMediaInDev: Exception %s',
                $e->getMessage()
            );
            $context = array('exception' => $e);
            $this->get('pluginlogger')->error($message, $context);
        }
    }

    /*
    * Covert http://example.com/media/exmaple/test.jpg to media/exmaple/test.jpg
    */
    public function checkUrl($url)
    {
        $path = explode('/media/', $url);
        $file = 'media/' . $path[1];
        $this->checkFile($file);
    }

    /*
    * Download given file if not exists e.g. media/exmaple/test.jpg
    */
    public function checkFile($file)
    {
        $fullpath = $this->getBasePath() . $file;
        $fullurl = $this->getDownloadUrl() . $file;

        //var_dump($fullpath);
        //var_dump($fullurl);

        if (!file_exists($fullpath)) {
            // create dir structure e.g. /media/exmaple/test.jpg (Yes, "test.jpg" will be a folder)
            mkdir($fullpath, 755, true);

            // delete file folder (e.g. test.jpg)
            rmdir($fullpath);

            $content = file_get_contents($fullurl);
            file_put_contents($fullpath, $content);
        }
    }

    public function getDownloadUrl()
    {
        if (!$this->downloadUrl) {
            $shop = false;
            if (Shopware()->Container()->initialized('shop')) {
                $shop = Shopware()->Container()->get('shop');
            }
            if (!$shop) {
                $shop = Shopware()->Container()->get('models')->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveDefault();
            }
            $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader')->getByPluginName('DevertMediaInDev', $shop);

            if ($config["download_url"] && $config["download_url"]!=='none') {
                $this->downloadUrl = $config["download_url"];
            }
        }

        return $this->downloadUrl;
    }

    public function getBasePath()
    {
        return Shopware()->DocPath();
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return Shopware()->Container()->get($name);
    }
}
