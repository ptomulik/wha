<?php
// Copyright (c) 2013 Pawel Tomulik <ptomulik@meil.pw.edu.pl>
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to 
// deal in the Software without restriction, including without limitation the 
// rights to use, copy, modify, merge, publish, distribute, sublicense, and/or 
// sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in 
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
// IN THE SOFTWARE


abstract class WHA_FileAnnotations 
{
    static private $s_mutable_annotations = array(
        'uuid',
        'created',
        'modified', 
        'created_by',
        'modified_by'
    );

    static private $s_immutable_annotations = array(
        'filetype'
    );

    private $uuid;
    private $created;
    private $modified;
    private $created_by;
    private $modified_by;

    abstract public function get_open_tag();
    abstract public function get_close_tag();

    // Get immutable annotations
    abstract public function get_filetype();
    // Get mutable annotations
    public function get_uuid() { return $this->uuid; }
    public function get_created() { return $this->created; }
    public function get_modified() { return $this->modified; }
    public function get_created_by() { return $this->created_by; }
    public function get_modified_by() { return $this->modified_by; }
    // Set mutable annotations
    public function set_uuid($val) { $this->uuid = $val; }
    public function set_created($val) { $this->created = $val; }
    public function set_modified($val) { $this->modified = $val; }
    public function set_created_by($val) { $this->created_by = $val; }
    public function set_modified_by($val) { $this->modified_by = $val; }

    // Get annotation by its name
    public function get_annotation($name)
    {
        if (in_array($name, $this->all_names())) {
            $getter = array($this, 'get_' . $name);
            if (is_callable($getter)) {
                return call_user_func($getter);
            }
        } else {
            trigger_error('WHA_FileAnnotations::get_annotation: invalid annotation "' 
                . $name .'"');
        }
    }
    // Set annotation by its name
    public function set_annotation($name, $val)
    {
        if (in_array($name, $this->mutable_names())) {
            $setter = array($this, 'set_' . $name);
            if (is_callable($setter)) {
                call_user_func($setter, $val);
            }
        } else {
            trigger_error('WHA_FileAnnotations::set_annotation: invalid annotation "' 
                . $name .'"');
        }
    }

    public function mutable_names()
    {
        return WHA_FileAnnotations::$s_mutable_annotations;
    }
    public function immutable_names()
    {
        return WHA_FileAnnotations::$s_immutable_annotations;
    }
    public function all_names()
    {
        return array_merge($this->mutable_names(), $this->immutable_names());
    }
}


// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
// vim: set foldmethod=marker foldcolumn=4:
?>
