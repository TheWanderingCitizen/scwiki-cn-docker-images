<?php
declare( strict_types=1 );

namespace Scwiki\Recovery;

class Hooks {
    public static function onSetup(): void {
        // Explicit extra handlers run after all upstream extension handlers.
        \MediaWiki\MediaWikiServices::getInstance()->getHookContainer()->register(
            'ScribuntoExternalLibraries', [ self::class, 'onLibraries' ]
        );
    }
    public static function onLibraries( string $engine, array &$libraries ): void {
        if ( $engine === 'lua' ) {
            $libraries['mw.ext.Apiunto'] = ApiuntoLibrary::class;
            $libraries['mw.smw'] = SemanticLibrary::class;
        }
    }
}
