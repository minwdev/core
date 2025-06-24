
<?php

namespace Gibbon\Database;

use Supabase\CreateClient;
use Gibbon\Database\Connection;
use Gibbon\Database\SupabaseConnection;

/**
 * Establish a Supabase Database Connection.
 */
class SupabaseConnector
{
    /**
     * Establish a database connection to Supabase.
     *
     * @param  array  $config          The database configuration
     * @param  bool   $throw_on_error  To throw error on connection issue or not. Default: false
     *
     * @return \Gibbon\Database\SupabaseConnection|bool
     */
    public function connect(array $config, bool $throw_on_error = false)
    {
        try {
            $supabaseUrl = $config['supabaseUrl'];
            $supabaseKey = $config['supabaseKey'];
            
            $supabase = CreateClient::create($supabaseUrl, $supabaseKey);
            
            return new SupabaseConnection($supabase, $config);
        } catch (\Exception $e) {
            if ($throw_on_error) {
                throw $e;
            }
            return false;
        }
    }
}
