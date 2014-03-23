<?php
/**
 * Created by PhpStorm.
 * User: fabi
 * Date: 3/23/14
 * Time: 11:02 AM
 */

namespace Fcj;


class Tree
{
    // todo: subarray($list, $select)

    /** Given a $list of "key-indexed n-tuples" (hence a PHP array),
     * that are e.g. rows retrieved from a database SELECT, build a tree
     * from a subset of those keyed-tuples whose keys are in the orderred-set
     * $levels.
     *
     * $levels is an ordered list of tree level names.
     * $list elements, the keyed-n-tuple are seen as paths of a tree.
     *
     * The overall principle is for each $list tuple to extract the $level-named nodes
     * and form the actual tree nodes from these; sort of.
     *
     * @param array|\Traversable $list
     * @param array $levels "A list of level names", ...
     * @param bool $doSlice Whether or not to strip off $levels from the $list.
     * @return array A tree.
     */
    public static function treeize($list, array $levels = array(), $doSlice=true)
    {
        assert(is_array($list) || $list instanceOf \Traversable);

        // If no tree level names were provided, we infer these from the first
        // element of $list :
        if (!$levels) {
            $levels = ValueMapper::getSettableProperties(reset($list));
            // Remove the last "level" name, so that the leafs of our tree are actual values,
            // instead of nulls (see below in loop: ...; $path=null; ...; $t = $path; ...) :
            end($levels); unset($levels[ key($levels) ] );
        }

        $root = array();
        $levels = array_fill_keys($levels, true);

        foreach($list AS $key => $path)
        {
            //$nodes = self::nodify($path, $levels);
            $nodes = array_intersect_key($path, $levels);

            // "STRAY" path are kept in $root[_stray] ; these are paths from which
            // we couldn't build a set of tree nodes.
            if (! $nodes) {
                $root['_stray'][ $key ] = $path;
                continue;
            }

            //
            // Below we're looping over the nodes that compose that path,
            // and populate the corresponding path from the $root of our tree.
            //

            $t =& $root;

            foreach($nodes AS $lname => $name) {
                if (! array_key_exists($name, $t))
                    $t[ $name ] = array();
                $t =& $t[ $name ];
            }

            if ($doSlice) {
                $path = array_diff_key($path, $nodes);
                $n = count($path);
                if (! $n)         $path = null;
                else if ($n == 1) $path = reset($path );
                // Else: path is a mapping of at least two elements.
            }

            // Note: This works since $t *is* a reference *to the last* (bottom-most)
            // node in the tree (~= end of the path).
            $t = $path;

            unset( $t );
        }

        return $root;
    }
}
