<?php
declare( strict_types=1 );

namespace Scwiki\Recovery;

use MediaWiki\Extension\Apiunto\ApiuntoLuaLibrary;
use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LuaError;

// Contract recovered from deployed main-25.09.12.40, Apiunto 3.0.0.
class ApiuntoLibrary extends ApiuntoLuaLibrary {
    public function register(): array {
        return $this->getEngine()->registerInterface( __DIR__ . '/mw.ext.Apiunto.lua', [
            'fetch' => [ $this, 'getRaw' ],
            'legacy' => [ $this, 'legacy' ],
        ] );
    }

    public function legacy( mixed $uri = null, mixed $arguments = [] ): array {
        global $wgApiuntoDefaultLocale, $wgRecoveryApiuntoSource;
        if ( !is_string( $uri ) || $uri === '' ) {
            throw new LuaError( 'Apiunto: identifier must be a non-empty string.' );
        }
        $arguments = is_array( $arguments ) ? $arguments : [];
        $locale = $arguments['locale'] ?? $wgApiuntoDefaultLocale ?? '';
        $include = $arguments['include'] ?? '';
        $processed = [
            'limit' => $arguments['limit'] ?? '',
            'page' => $arguments['page'] ?? '',
            'locale' => is_array( $locale ) ? implode( ',', array_map( 'strval', $locale ) ) : (string)$locale,
            'include' => is_array( $include ) ? implode( ',', array_map( 'strval', $include ) ) :
                ( is_string( $include ) ? $include : '' ),
        ];
        // The deployed fork forwarded only these four non-empty arguments.
        return parent::getRaw( $wgRecoveryApiuntoSource, $uri, array_filter( $processed ) );
    }
}
