<?php

namespace Fcj;


/**
 * Utility functions for manipulation of table-like arrays, "table" in the spirit of database tables,
 * hence « rigidly-structured uniform set of rows ».
 *
 * @author cadet.fabien@gmail.com
 * @since 2014-03-20
 */
class Table
{
    /**
     * Extend a $left "table" with a $right table, much in the spirit of
     * an SQL LEFT JOIN : Each "row" in $left is extended (merged) with a
     * matching row in $right if it exists.
     *
     * @since Fcj.2014-03-20 Initial basic impl.
     *
     * @param array|\Traversable $left
     * @param array|\ArrayAccess $right
     * @param bool $onKey TRUE ; TODO: Impl. false case.
     * @param array $mapping TODO: NOT IMPL..
     * @throws \RuntimeException
     * @return array The resulting "joined" table.
     */
    public static function extend($left, $right, $onKey=true, $mapping=array())
    {
        assert(is_array($left)  || $left instanceOf \Traversable);
        assert(is_array($right) || $right instanceOf \ArrayAccess);

        if (!empty($mapping)) // todo!
            throw new \RuntimeException(
                "ERROR: MISSING IMPLEMENTATION of \$mapping @ "
                . __METHOD__ .':'.__LINE__);

        // We want $left to be the tallest hashmap, swap!
        if (count($left) < count($right))
            return self::extend($right, $left, $onKey, $mapping);

        $rows = array();
        $rl = $rr = null;

        // Find out the "column" names in the $right table :
        $rightNulls = !empty($right) ? array_fill_keys(array_keys(reset($right)), null) : null;

        if ($onKey) {
            foreach ($left AS $kl => $rl) {
                // Lookup a matching row in $right with key $kl :
                if (isset($right[$kl]))
                    $rr = $right[$kr = $kl]; // Note: $kr is indeed unused anywhere.
                // If not exists, we attempt to extend $rl anyway with some key-->nulled-value pairs
                // from an eventual right row structure that we got from a previous iteration.
                // Note: We're initializing $rightStruct once here so as to save cycles on array_fill_keys()
                // in case $right array is signifficantly smaller than $left.
                else if ($rightNulls)
                    $rr = $rightNulls;

                // NOTE/warning: See @link http://www.php.net/manual/function.array-merge.php
                // Same string keys get overwritten!!
                $row = array_merge($rl, $rr);
                $rows[$kl] = $row;
            }
        }
        else
            throw new \RuntimeException(
                "ERROR: MISSING IMPLEMENTATION of \$onKey == FALSE @ "
                . __METHOD__ .':'.__LINE__);

        return $rows;
    }
} 