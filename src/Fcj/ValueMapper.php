<?php
/** File: src/Fcj/ValueMapper.php
 *
 * fci.2013-08-28
 */
namespace Fcj;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface AS PropertyAccessException;

/** Mapping things to other things.
 *
 * todo/?: sliceObject()? (e.g. for getting rid of proxies).
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

    /** An enhanced version of @see map where $ppaths may be an empty list of
     * property paths if one wants to get this infered from settable stuff
     * common to $source *and* $target.
     *
     * NOTE: A simple use case of this is map2($whatever) so as to retrive the
     *       values of settable $whatever properties.
     *
     * @param mixed $source Typically an object or array, possibly a \Traversable (not tested).
     * @param object|array $target
     * @param array $ppaths An eventually empty list of property paths pairs (key=>value).
     * @param array $filter
     * @param bool $magics Whether or not to consider the presence of PHP class magics __set / __call as implicit setters, defaults to false.
     * @return mixed $target gets returned.
     */
    public static function map2(
        $source, $target = array(),
        array $ppaths = array(),
        array $filter = array(),
        $magics=true)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        // If no property path mappings list are provided, infer it :
        if (! $ppaths) {
            $s = self::getSettableProperties($source, $magics);
            // If $target is an empty array, a map of source key to null, as
            // these get infered as '[key]' ppath later in the below loop :
            if (is_array($target) && !$target)
                $t = $s;
            // Else the property paths list is the common settable properties
            // from $source & $target :
            else
                $t = self::getSettableProperties($target, $magics);
            $u = array_intersect($s, $t);
            $u = array_diff($u, $filter);
            $ppaths = array_combine($u, $u);
            unset($s, $t, $u);
        }

        $target_ia = is_array($target);
        $source_ia = is_array($source);

        // Map data loop :
        foreach ($ppaths AS $from => $to) {
            $to   = $target_ia ? "[$to]"   : $to;
            $from = $source_ia ? "[$from]" : $from;
            $value = $accessor->getValue($source, $from);
            $accessor->setValue($target, $to, $value);
        }

        return $target;
    }

    /** Retrieve the list of $source object (or array) properties that
     * are set-able.
     *
     * TODO: See if a \Traversable thing would work too.
     *
     * @param array|object $source
     * @param bool $magics If true, PHP class magics __set / __call on $source entail that properties do not need settters, default to false.
     * @return array list<string> of property names (or array keys).
     */
    public static function getSettableProperties($source, $magics=false)
    {
        $props = array();
        if (is_array($source)) {
            $props = array_keys($source);
        }
        else if (is_object($source)) {
            $reflC = new \ReflectionClass($source);
            // Read the list of properties of it :
            $props = array_map(function(\ReflectionProperty $p) {
                return $p->getName();
            }, $reflC->getProperties());
            // Filter out properties that do not have setX() :
            if (!$magics || (!$reflC->hasMethod('__set') && !$reflC->hasMethod('__call'))) {
                $props = array_filter($props, function($p) use ($reflC) {
                    $setter = 'set' . Inflector::camelize($p);
                    return $reflC->hasMethod($setter);
                });
            }
        }
        return $props;
    }

    /** Populate "missing" (made public) properties from object $obj with value $value.
     *
     * @param object $obj
     * @param array $ppaths
     * @param mixed $value
     * @return array
     * @author fabien.cadet@cines.fr
     */
    public static function populatePublicProperties($obj, array $ppaths, $value=null)
    {
        assert( is_object($obj) );
        $accessor = PropertyAccess::createPropertyAccessor();
        $populated = array();
        foreach ($ppaths AS $from => $to) {
            $to = $to ? : "[$from]";
            // Attempt to read *AND* write value :
            try {
                $v = $accessor->getValue($obj, $to);
                $accessor->setValue($obj, $to, $v);
            }
            // Whatever we caught => add a property named '$to' set to Null :
            catch(PropertyAccessException $ex) {
                $obj->$to = $value;
                $populated[ $from ] = $to;
            }
        }
        return $populated;
    }
}