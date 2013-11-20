<?php
/** File: src/Fcj/ValueMapper.php
 *
 * fci.2013-08-28
 */
namespace Fcj;

use Symfony\Component\PropertyAccess\PropertyAccess;


/** Mapping things to other things.
 *
 * Class ValueMapper
 * @package Fcj
 */
class ValueMapper
{
    /** Map property-path indexed values of $data to $target, where $target
     * shall be something that can be manipulated by the PropertyAccess component
     * (hence typically an object, or an array).
     *
     * @param mixed $target Typically an object, or an array.
     * @param \Traversable $data Typically a mapping of property paths to values.
     */
    public static function map($target, $data)
    {
        $accessor = PropertyAccess::getPropertyAccessor();
        foreach ($data AS $path => $value) {
            $accessor->setValue($target, $path, $value);
        }
        //var_dump($target, $data, $accessor);
        return; // todo??
    }

    /** Re-map, initially the purpose was to have a dual of map($target, $data), it ends up being
     * more general : Maps $source stuff over $target based of an list of (from ; to) pairs defined
     * as $ppaths (key->value).
     *
     * Fixme: map() shall disappear in favor of this one func.
     *
     * @param  mixed $source Anything that the PropertyPath component can work with, typically an object or array.
     * @param  array $ppaths A mapping of property paths over $source, to property paths over $target.
     * @param  array $target Likewise $source, defaults to array().
     * @return mixed $target.
     *
     * Fixme: //public static function remap($source, \Traversable $ppaths, $target = array())
     */
    public static function remap($source, array $ppaths, $target = array())
    {
        $accessor = PropertyAccess::getPropertyAccessor();
        foreach ($ppaths AS $from => $to) {
            $to = $to ? : "[$from]";
            $value = $accessor->getValue($source, $from);
            $accessor->setValue($target, $to, $value);
        }
        return $target;
    }
}