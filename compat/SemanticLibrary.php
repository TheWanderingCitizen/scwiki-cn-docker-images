<?php
declare( strict_types=1 );

namespace Scwiki\Recovery;

// The production VehicleHardpoint module explicitly requests 1000 rows.
// Preserve that call with two ordinary inline queries, never a global limit raise.
class SemanticLibrary extends \SMW\Scribunto\ScribuntoLuaLibrary {
    public function ask( $arguments = null ): array {
        if ( !is_array( $arguments ) || !in_array( 'limit=1000', $arguments, true ) ) {
            return parent::ask( $arguments );
        }
        $offset = 0;
        $query = [];
        foreach ( $arguments as $argument ) {
            if ( is_string( $argument ) && preg_match( '/^offset=(\d+)$/', $argument, $match ) ) {
                $offset = (int)$match[1];
            } elseif ( $argument !== 'limit=1000' ) {
                $query[] = $argument;
            }
        }
        $rows = [];
        for ( $page = 0; $page < 2; $page++ ) {
            $result = parent::ask( array_merge( $query, [ 'limit=500', 'offset=' . ( $offset + $page * 500 ) ] ) )[0];
            if ( !is_array( $result ) ) { return [ $result ]; }
            $rows = array_merge( $rows, array_values( $result ) );
            if ( count( $result ) < 500 ) { break; }
        }
        return [ $rows ? array_combine( range( 1, count( $rows ) ), $rows ) : [] ];
    }
}
