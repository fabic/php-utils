<?php
/** File: src/Fcj/Reindex.php
 *
 * fcj.2013 April : Some old school procedural-minded utility functions.
 *
 * fcj.2013-08-28 : Imported from fabic's Nymfony "project" :
 *    https://github.com/fabic/Nymfony/blob/72c84550cf88d8b78b1d5eaa91b894524576077f/src/Fcj/Util.php
 *
 * © 2013 fabic.github.com <cadet.fabien@gmail.com>
 */
namespace Fcj;

use Symfony\Component\PropertyAccess\PropertyAccess;


/**
 * Class Reindex
 * @package Fcj
 */
class Reindex
{
    /** Re-indexes a $list of things by/-following ... ~~ a list of property paths ~~.
     *
     * @see http://localhost:8000/doc/apigen-ed/class-Symfony.Component.PropertyAccess.PropertyAccess.html
     * @see http://symfony.com/blog/new-in-symfony-2-2-new-propertyaccess-component
     *
     * @param \Traversable $list A list of things.
     * @param string ... A list of property paths.
     * @return array A new list.
     */
    public static function reindex($list)
    {
        $ppaths = func_get_args();
        array_shift($ppaths);
        if (empty($ppaths))
            return $list;

        $retval = array();
        $path = array_shift($ppaths);
        //$accessor = PropertyAccess::getPropertyAccessor();
	// TODO: Have it be static so as to avoid multiple instanciation? (such as in Symfony2)
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($list AS $item) {
            //try {
            $index = $accessor->getValue($item, $path);
            if ($item instanceOf \Traversable) // FIXME ?
            $retval[$index] = self::reindex($item, $ppaths);
            else
                $retval[$index] = $item;
            //catch($ex) {
            //}
        }

        return $retval;
    }

    /**
     * @param \Traversable $list
     * @return array
     * @link http://www.php.net/manual/en/language.oop5.magic.php#object.invoke
     */
    public function __invoke($list)
    {
        $args = func_get_args();
        return call_user_func_array('self::reindex', $args);
    }
}

/** Ooold impl.
 *
 *
function array_reindex(Array $a)
{
    $keys = func_get_args();
    array_shift($keys);
    if (empty($keys))
        return $a;
    $retval = array();
    $k = array_shift($keys);
    foreach ($a as $b) {
        if (array_key_exists($k, $b)) {
            $index = $b[$k];
            $retval[$index] = call_user_func_array('array_reindex', $keys);
        } else error_log(__FUNCTION__ . "WARNING: Key '$k' not found!");
    }
    return $retval;
}

*/

