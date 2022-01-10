<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Config extends Model
{
	public static $tournament = null;
	public static $ranks      = null;
	public static $divisions  = null;
	public const  KEY_BELT_RANKS              = 'belt_ranks';
	public const  KEY_DIVISION_WEIGHT_CLASSES = 'division_weight_classes';
	public const  KEY_TOURNAMENT              = 'tournament';

	protected $table  = 'config';

	/**
	 * Reads the tournament configuration
	 */
	public static function read() {
		if( ! is_null( Config::$tournament )) { return; }
		Config::$tournament = Config::where( 'criteria->key', '=', KEY_TOURNAMENT )->first()->pluck( 'value' );
		if( Config::$tournament === null ) { die( "Tournament settings have not been configured in FS5 DB config table." ); }
		Config::$tournament = json_decode( Config::$tournament, true);

		Config::read_ranks();
		Config::read_divisions();
	}

	/**
	 * Applies query criteria; e.g. given a division, find eligible athletes
	 */
	public static function apply_criteria( $query, $criteria, $key, $options = [] ) {
		if( ! isset( $criteria[ $key ])) { return $query; }
		if( is_string( $criteria[ $key ] ) || is_numeric( $criteria[ $key ] )) {
			if( isset( $options[ 'or' ]) && $options[ 'or' ]) {
				return $query->orWhere( $key, '=', $criteria[ $key ]);
			} else {
				return $query->where( $key, '=', $criteria[ $key ]);
			}

		} else if( is_array( $criteria[ $key ] )) {
			// Range 
			if( array_key_exists( 'min', $criteria[ $key ]) && array_key_exists( 'max', $criteria[ $key ])) {
				if( ! is_null( $criteria[ $key ][ 'min' ])) {
					$query->where( $key, '>=', $criteria[ $key ][ 'min' ]);
				}
				if( ! is_null( $criteria[ $key ][ 'max' ])) {
					$query->where( $key, '<=', $criteria[ $key ][ 'max' ]);
				}
				return $query;

			// Sets of values or ranges
			} else {
				$values = [];
				$ranges = [];
				foreach( $criteria[ $key ] as $c ) {
					// Value
					if( is_string( $c ) || is_numeric( $c )) {
						array_push( $values, $c );

					// Range
					} else if( is_array( $c ) && array_key_exists( 'min', $c ) && array_key_exists( 'max', $c )) {
						array_push( $ranges, $c );
					}
				}
				return $query->where( function( $query ) use( $criteria, $key, $values, $ranges ) {
					// Apply subquery for list of values
					if( count( $values ) > 0 ) {
						$query->whereIn( $key, $values );
					}

					// Apply subquery for list of ranges
					foreach( $ranges as $range ) {
						$query->orWhere( function( $query ) use( $key, $range ) {
							if( ! is_null( $range[ 'min' ])) {
								$query->where( $key, '>=', $range[ 'min' ]);
							}
							if( ! is_null( $range[ 'max' ])) {
								$query->where( $key, '<=', $range[ 'max' ]);
							}
						});
					}
					return $query;
				});
			}
		}
	}

	/**
	 * Converts age ranges to dates
	 */
	public static function age_range_to_dates( $range, &$criteria, $key ) {
		$year = date( 'Y' );
		$yearmin = $year - $range[ 'max' ];
		$yearmax = $year - $range[ 'min' ];
		$criteria[ $key ] = [];
		if( $range[ 'max' ]) {
			$criteria[ $key ][ 'min' ] = "{$yearmin}-01-01";
		} else {
			$criteria[ $key ][ 'min' ] = null;
		}
		if( $range[ 'min' ]) {
			$criteria[ $key ][ 'max' ] = "{$yearmax}-12-31";
		} else {
			$criteria[ $key ][ 'max' ] = null;
		}
	}

	/**
	 * Converts rank ranges to lists
	 */
	public static function rank_range_to_list( $range, &$criteria, $key ) {
		if( is_array( $range )) {
			$colors = array_map( function( $rank ) { return $rank[ 'color' ]; }, Config::$ranks );
			$i      = array_search( $range[ 'min' ], $colors );
			$j      = array_search( $range[ 'max' ], $colors );
			$criteria[ $key ] = [];
			foreach( range( $i, $j ) as $k ) {
				array_push( $criteria[ $key ], $colors[ $k ]);
			}
		} else {
			$criteria[ $key ] = [ $range ];
		}
	}

	/**
	 * Reads the divisions for the tournament
	 * @depends read, read_ranks
	 */
	private static function read_divisions() {
		if( ! is_null( Config::$divisions )) { return; }
		$org       = Config::$tournament[ 'settings' ][ KEY_DIVISION_WEIGHT_CLASSES ];
		Config::$divisions = Config::read_settings( KEY_DIVISION_WEIGHT_CLASSES, $org );
	}

	/**
	 * Reads the belt rank information for the tournament
	 */
	private static function read_ranks() {
		if( ! is_null( Config::$ranks )) { return; }
		$org   = Config::$tournament[ 'settings' ][ KEY_BELT_RANKS ];
		Config::$ranks = Config::read_settings( KEY_BELT_RANKS, $org );
	}

	/**
	 * Reads the configuration settings for the tournament
	 */
	private static function read_settings( $key, $org ) {
		$settings = Config::where( 'criteria->key', '=', $key )->where( 'criteria->org', '=', $org )->first()->pluck( 'value' );
		if( $settings === null ) { die( "{$key} settings are not defined for organization = '{$org}'" ); }
		$settings = json_decode( $settings, true );
	}

}
