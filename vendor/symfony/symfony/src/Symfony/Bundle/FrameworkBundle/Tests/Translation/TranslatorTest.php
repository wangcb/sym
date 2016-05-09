<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageSelector;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir;

    protected function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf2_translation';
        $this->deleteTmpDir();
    }

    protected function tearDown()
    {
        $this->deleteTmpDir();
    }

    protected function deleteTmpDir()
    {
        if (!file_exists($dir = $this->tmpDir)) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    public function testTransWithoutCaching()
    {
        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));

        $this->assertEquals('foo (FR)', $translator->trans('foo'));
        $this->assertEquals('bar (EN)', $translator->trans('bar'));
        $this->assertEquals('foobar (ES)', $translator->trans('foobar'));
        $this->assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        $this->assertEquals('no translation', $translator->trans('no translation'));
        $this->assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        $this->assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
        $this->assertEquals('foobarbaz (fr.UTF-8)', $translator->trans('foobarbaz'));
        $this->assertEquals('foobarbax (sr@latin)', $translator->trans('foobarbax'));
    }

    public function testTransWithCaching()
    {
        // prime the cache
        $translator = $this->getTranslator($this->getLoader(), array('cache_dir' => $this->tmpDir));
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));

        $this->assertEquals('foo (FR)', $translator->trans('foo'));
        $this->assertEquals('bar (EN)', $translator->trans('bar'));
        $this->assertEquals('foobar (ES)', $translator->trans('foobar'));
        $this->assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        $this->assertEquals('no translation', $translator->trans('no translation'));
        $this->assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        $this->assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
        $this->assertEquals('foobarbaz (fr.UTF-8)', $translator->trans('foobarbaz'));
        $this->assertEquals('foobarbax (sr@latin)', $translator->trans('foobarbax'));

        // do it another time as the cache is primed now
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader->expects($this->never())->method('load');

        $translator = $this->getTranslator($loader, array('cache_dir' => $this->tmpDir));
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));

        $this->assertEquals('foo (FR)', $translator->trans('foo'));
        $this->assertEquals('bar (EN)', $translator->trans('bar'));
        $this->assertEquals('foobar (ES)', $translator->trans('foobar'));
        $this->assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        $this->assertEquals('no translation', $translator->trans('no translation'));
        $this->assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        $this->assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
        $this->assertEquals('foobarbaz (fr.UTF-8)', $translator->trans('foobarbaz'));
        $this->assertEquals('foobarbax (sr@latin)', $translator->trans('foobarbax'));
    }

    public function testRefreshCacheWhenResourcesAreNoLongerFresh()
    {
        $resource = $this->getMock('Symfony\Component\Config\Resource\ResourceInterface');
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $resource->method('isFresh')->will($this->returnValue(false));
        $loader
            ->expects($this->exactly(2))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('fr', array(), array($resource))));

        // prime the cache
        $translator = $this->getTranslator($loader, array('cache_dir' => $this->tmpDir, 'debug' => true));
        $translator->setLocale('fr');
        $translator->trans('foo');

        // prime the cache second time
        $translator = $this->getTranslator($loader, array('cache_dir' => $this->tmpDir, 'debug' => true));
        $translator->setLocale('fr');
        $translator->trans('foo');
    }

    public function testTransWithCachingWithInvalidLocale()
    {
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $translator = $this->getTranslator($loader, array('cache_dir' => $this->tmpDir), '\Symfony\Bundle\FrameworkBundle\Tests\Translation\TranslatorWithInvalidLocale');
        $translator->setLocale('invalid locale');

        $this->setExpectedException('\InvalidArgumentException');
        $translator->trans('foo');
    }

    public function testGetLocale()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request
            ->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en'))
        ;

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container
            ->expects($this->exactly(2))
            ->method('isScopeActive')
            ->with('request')
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $container
            ->expects($this->once())
            ->method('has')
            ->with('request')
            ->will($this->returnValue(true))
        ;

        $container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request))
        ;

        $translator = new Translator($container, new MessageSelector());

        $this->assertNull($translator->getLocale());
        $this->assertSame('en', $translator->getLocale());
    }

    public function testGetLocaleWithInvalidLocale()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request
            ->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('foo bar'))
        ;
        $request
            ->expects($this->once())
            ->method('getDefaultLocale')
            ->will($this->returnValue('en-US'))
        ;

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container
            ->expects($this->once())
            ->method('isScopeActive')
            ->with('request')
            ->will($this->returnValue(true))
        ;

        $container
            ->expects($this->once())
            ->method('has')
            ->with('request')
            ->will($this->returnValue(true))
        ;

        $container
            ->expects($this->any())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request))
        ;

        $translator = new Translator($container, new MessageSelector());
        $this->assertSame('en-US', $translator->getLocale());
    }

    public function testDifferentCacheFilesAreUsedForDifferentSetsOfFallbackLocales()
    {
        /*
         * Because the cache file contains a catalogue including all of its fallback
         * catalogues, we must take the active set of fallback locales into
         * consideration when loading a catalogue from the cache.
         */
        $translator = $this->createTranslator(new ArrayLoader(), array('cache_dir' => $this->tmpDir));
        $translator->setLocale('a');
        $translator->setFallbackLocales(array('b'));
        $translator->addResource('loader', array('foo' => 'foo (a)'), 'a');
        $translator->addResource('loader', array('bar' => 'bar (b)'), 'b');

        $this->assertEquals('bar (b)', $translator->trans('bar'));

        // Remove fallback locale
        $translator->setFallbackLocales(array());
        $this->assertEquals('bar', $translator->trans('bar'));

        // Use a fresh translator with no fallback locales, result should be the same
        $translator = $this->createTranslator(new ArrayLoader(), array('cache_dir' => $this->tmpDir));
        $translator->setLocale('a');
        $translator->addResource('loader', array('foo' => 'foo (a)'), 'a');
        $translator->addResource('loader', array('bar' => 'bar (b)'), 'b');

        $this->assertEquals('bar', $translator->trans('bar'));
    }

    protected function getCatalogue($locale, $messages, $resources = array())
    {
        $catalogue = new MessageCatalogue($locale);
        foreach ($messages as $key => $translation) {
            $catalogue->set($key, $translation);
        }
        foreach ($resources as $resource) {
            $catalogue->addResource($resource);
        }

        return $catalogue;
    }

    protected function getLoader()
    {
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader
            ->expects($this->at(0))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('fr', array(
                'foo' => 'foo (FR)',
            ))))
        ;
        $loader
            ->expects($this->at(1))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('en', array(
                'foo' => 'foo (EN)',
                'bar' => 'bar (EN)',
                'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
            ))))
        ;
        $loader
            ->expects($this->at(2))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('es', array(
                'foobar' => 'foobar (ES)',
            ))))
        ;
        $loader
            ->expects($this->at(3))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('pt-PT', array(
                'foobarfoo' => 'foobarfoo (PT-PT)',
            ))))
        ;
        $loader
            ->expects($this->at(4))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('pt_BR', array(
                'other choice' => '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
            ))))
        ;
        $loader
            ->expects($this->at(5))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('fr.UTF-8', array(
                'foobarbaz' => 'foobarbaz (fr.UTF-8)',
            ))))
        ;
        $loader
            ->expects($this->at(6))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('sr@latin', array(
                'foobarbax' => 'foobarbax (sr@latin)',
            ))))
        ;

        return $loader;
    }

    protected function getContainer($loader)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($loader))
        ;

        return $container;
    }

    public function getTranslator($loader, $options = array(), $translatorClass = '\Symfony\Bundle\FrameworkBundle\Translation\Translator')
    {
        $translator = $this->createTranslator($loader, $options, $translatorClass);

        $translator->addResource('loader', 'foo', 'fr');
        $translator->addResource('loader', 'foo', 'en');
        $translator->addResource('loader', 'foo', 'es');
        $translator->addResource('loader', 'foo', 'pt-PT'); // European Portuguese
        $translator->addResource('loader', 'foo', 'pt_BR'); // Brazilian Portuguese
        $translator->addResource('loader', 'foo', 'fr.UTF-8');
        $translator->addResource('loader', 'foo', 'sr@latin'); // Latin Serbian

        return $translator;
    }

    private function createTranslator($loader, $options, $translatorClass = '\Symfony\Bundle\FrameworkBundle\Translation\Translator')
    {
        $translator = new $translatorClass(
            $this->getContainer($loader),
            new MessageSelector(),
            array('loader' => array('loader')),
            $options
        );

        return $translator;
    }
}

class TranslatorWithInvalidLocale extends Translator
{
    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
