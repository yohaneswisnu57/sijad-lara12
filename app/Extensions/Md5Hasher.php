<?php

namespace App\Extensions;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Hashing\AbstractHasher;

class Md5Hasher extends AbstractHasher implements HasherContract
{
    /**
     * Create a new hash configuration.
     *
     * @param  array  $options
     * @return void
     */
    public function __construct(array $options = [])
    {
        //
    }

    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        return md5($value);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        // Gunakan hash_equals untuk mencegah timing attack
        // Pastikan format hash di DB dibandingkan dengan hasil md5 input
        return hash_equals($hashedValue, md5($value));
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return false; // Jangan pernah rehash ke algoritma lain karena DB legacy shared
    }
}
