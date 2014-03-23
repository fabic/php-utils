<?php
/**
 * Created by PhpStorm.
 * User: fabi
 * Date: 3/23/14
 * Time: 11:02 AM
 */

namespace Fcj;

/**
 * Utility stuff work working with tree-like data.
 *
 * @since 2014-03-23 New. treeize() & nodify().
 * @author cadet.fabien@gmail.com
 */
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

        // The "root" nodes' children list, indexed by child name :
        $root = array();

        foreach($list AS $key => $path)
        {
            $nodes = self::nodify($path, $levels, $doSlice);

            // "STRAY" path are kept in $root[_stray] ; these are paths from which
            // we couldn't build a set of tree nodes.
            if (! $nodes) {
                $root['_stray'][ $key ] = $path;
                continue;
            }

            // $nodes here is itself a standalone tree with only one path,
            // we're here retrieving the root node $name and descendants $subtree
            // for later insertion in $root below.
            list($name, $subtree) = each($nodes);

            // Create vertice in $root if not exists with an empty set of children :
            if (!array_key_exists($name, $root))
                $root[ $name ] = array();

            //
            // Append children nodes :

            //$root[$name] += $subtree;
            // ^ see '+' array operator : @link http://www.php.net/manual/en/language.operators.array.php
            // existing $subtree keys (nodes) are kept, not overwritten -- silently.

            // won't work either as it will re-index numerical indexes :
            //$root[$name] = array_merge_recursive($root[$name], $subtree);

            // this one will do :
            $root[$name] = array_replace_recursive($root[$name], $subtree);
        }

        return $root;
    }

    /** Utility function for the above @see treeize, whose purpose is to come up
     * with a list of "nodes" from $path, given the tree level names $levels.
     *
     * @param array $path A keyed-n-tuple (i.e. a map), of which a subset of key-value pairs ($levels) represent a path in a tree.
     * @param array $levels The tree level names (ordered set); these are keys in $path keyed-n-tuple.
     * @param bool $doSlice Whether or not leafs are made up of whole $path, or if $levels shall be sliced off.
     * @return array A tree with one path.
     */
    public static function nodify($path, array $levels, $doSlice=true)
    {
        // $nodes is the subset of $path whose keys are in $levels;
        // $nodes here is actually the a set of key-value pairs where
        // key is the tree level name, and value is the node value "at that level".
        $levels = array_fill_keys($levels, true);
        $nodes = array_intersect_key($path, $levels);

        $root = array();

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

        return $root;
    }
}
