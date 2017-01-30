<?php
/**
 * TransSub view helper
 * For translation of subject to other language for building disjunct search query (subject in czech OR english)
 */
namespace VuFind\View\Helper\Root;
use Zend\I18n\Translator\TranslatorInterface;

/**
 * Translate Subject view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Daniel Mareček
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class TransSub extends \Zend\View\Helper\AbstractHelper
{
    /**
     * Translator (or null if unavailable)
     *
     * @var TranslatorInterface
     */
    protected $translator = null;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator Sub VuFind translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Translate a string
     *
     * @param string $str String to escape and translate
     *               $to Language for translation
     * @return string
     */
    public function __invoke($str, $to)
    {
        try {
            $this->translator->setLocale($to);
        } catch (\Zend\Mvc\Exception\BadMethodCallException $e) {
            if (!extension_loaded('intl')) {
                throw new \Exception(
                    'Translation broken due to missing PHP intl extension.'
                    . ' Please disable translation or install the extension.'
                );
            }
        }

        return $this->view->escapeHtml($this->translator->translate($str));
    }
}
