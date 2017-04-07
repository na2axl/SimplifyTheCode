<?php

    /**
     * STC - Simplify The Code
     *
     * An open source application development framework for PHP
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2015 - 2016, Centers Technologies
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     * @package     STC
     * @author      Nana Axel
     * @copyright   Copyright (c) 2015 - 2016, Centers Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * Benchmark Class
     *
     * @package     STC
     * @subpackage  Utilities
     * @category    Benchmark
     * @author      Nana Axel
     */
    class STC_Benchmark
    {

        /**
         * The benchmark
         * @var array
         * @access private
         */
        private static $marker = array( ) ;

        /**
         * The state of the profiler
         * @var bool
         * @access private
         */
        private static $profiling = false;

        /**
         * Dump the profiler as an array
         * @const int
         */
        const DUMP_ARRAY = 0;

        /**
         * Dump the profiler as a JSON
         * @const int
         */
        const DUMP_JSON = 1;

        /**
         * Add a benchmark point.
         * @param  string  $name  The name of the benchmark point
         * @return void
         */
        public function mark( $name )
        {
            self::$marker[$name]['e'] = microtime( ) ;
            self::$marker[$name]['m'] = memory_get_usage( ) ;
        }

        /**
         * Start the profiler.
         * @throws Exception If the forp extension is not installed.
         * @return void
         */
        public function start_profiler( )
        {
            if (config_item('enable_profiler')) {
                if (!self::$profiling) {
                    if (function_exists('forp_start')) {
                        forp_start();
                        self::$profiling = true;
                    }
                    else {
                        $error = 'Unable to start the profiler. Make sure you have installed the forp PHP extension.';
                        log_message('error', $error);
                        throw new Exception($error);
                    }
                }
                else {
                    log_message('error', 'Trying to start the profiler twice.');
                }
            }
        }

        /**
         * Stop the profiler.
         * @throws Exception If the forp extension is not installed.
         * @return void
         */
        public function stop_profiler( )
        {
            if (config_item('enable_profiler')) {
                if (self::$profiling) {
                    if (function_exists('forp_end')) {
                        forp_end();
                        self::$profiling = false;
                    }
                    else {
                        $error = 'Unable to stop the profiler. Make sure you have installed the forp PHP extension.';
                        log_message('error', $error);
                        throw new Exception($error);
                    }
                }
                else {
                    log_message('error', "Can't stop the profiler. The profiler was not started.");
                }
            }
        }

        /**
         * Dump the profiler results.
         * @return  array  If $dump_type is Benchmark::DUMP_ARRAY
         * @return  string  If $dump_type is Benchmark::DUMP_JSON
         */
        public function dump_profiler( $dump_type = NULL )
        {
            if (self::$profiling) {
                log_message('error', "Can't dump the profiler while profiling.");
                return NULL;
            }
            else {
                switch ($dump_type) {
                    default:
                    case self::DUMP_ARRAY:
                        return forp_dump();

                    case self::DUMP_JSON:
                        return json_encode(forp_dump());
                }
            }
        }

        /**
         * Calculate the elapsed time between two benchmark points.
         * @param  string  $point1    The name of the first benchmark point
         * @param  string  $point2    The name of the second benchmark point
         * @param  int     $decimals
         * @return mixed
         */
        public function elapsed_time( $point1 = NULL, $point2 = NULL, $decimals = 4 )
        {
            if ( $point1 === NULL || !array_key_exists( $point1, self::$marker ) ) {
                return 0 ;
            }

            if ( !array_key_exists( $point2, self::$marker ) ) {
                $this->mark( $point2 );
            }

            list( $sm, $ss ) = explode( ' ', self::$marker[$point1]['e'] ) ;
            list( $em, $es ) = explode( ' ', self::$marker[$point2]['e'] ) ;

            return number_format( ( $em + $es ) - ( $sm + $ss ), $decimals ) ;
        }

        /**
         * Calculate the memory usage of a benchmark point
         * @param  string  $point1    The name of the first benchmark point
         * @param  string  $point2    The name of the second benchmark point
         * @param  int     $decimals
         * @return mixed
         */
        public function memory_usage( $point1 = NULL, $point2 = NULL, $decimals = 4 )
        {
            if ( $point1 === NULL || !array_key_exists( $point1, self::$marker ) ) {
                return 0 ;
            }

            if ( !array_key_exists( $point2, self::$marker ) ) {
                $this->mark( $point2 );
            }

            $sm = self::$marker[$point1]['m'] ;
            $em = self::$marker[$point2]['m'] ;

            return number_format( $em - $sm , $decimals ) ;
        }

    }