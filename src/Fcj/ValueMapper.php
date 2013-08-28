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
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach($data AS $path => $value) {
            $accessor->setValue($target, $path, $value);
        }
        //var_dump($target, $data, $accessor);
    }
}