namespace Kovaloff\LaravelCognitoAuth;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer Plugin Class
 *
 */
class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'post-autoload-dump' => array(
                array('saveRevisionId', 0)
            ),
            'post-package-update' => array(
                array('saveRevisionId', 0)
            ),
            'post-package-install' => array(
                array('saveRevisionId', 0)
            ),
        );
    }
    
    public static function saveRevisionId()
    {
        $revId = shell_exec('cd ' . __DIR__ . '; git rev-parse --short HEAD');
        file_put_contents(__DIR__ . '/revision.txt', $revId);
    }
}
