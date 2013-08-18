<?php

/**
 * Class DependencyArray
 *
 * This is a modified array that orders keywords based on
 * their priority relative to other keywords
 */
class DependencyArray {
    protected $depArr = array();

    /**
     * @param $keyword
     * @param int $start
     *
     * Create the starting keyword
     */
    public function __construct($keyword = "", $start = 0)
    {
        if (!empty($keyword))
            $this->add($keyword);
    }

    public function add($keyword, $dependency = "")
    {
        if ($keyword != $dependency) {
            if (!empty($dependency)) {
                if ($depCoors = $this->search($dependency)) {
//                    var_dump($keyword, $dependency);
//                    echo "<pre>BEFORE:";print_r($this->depArr);echo "</pre><br />";
//                    var_dump("depCoors:", $depCoors); echo "<br />";
                    if ($keyCoors = $this->search($keyword)) {
//                        var_dump("keyCoors:", $keyCoors, "actual data:",$this->depArr[$keyCoors->priority][$keyCoors->index]);
                        unset($this->depArr[$keyCoors->priority][$keyCoors->index]);
                    }

                    $this->depArr[$depCoors->priority+1][] = $keyword;
                    $this->depArr[$depCoors->priority][] = $dependency;

//                    echo "<pre>AFTER:";print_r($this->depArr);echo "</pre>";

                } else {
                    $this->depArr[0][] = $dependency;
                    $this->depArr[1][] = $keyword;
                }
            } else {
                if (!$this->search($keyword, false))
                    $this->depArr[0][] = $keyword;
            }
        }

        foreach ($this->depArr as $i => $arr) {
            $this->depArr[$i] = array_values(array_unique($arr));
        }
    }

    public function moveUp($keyword)
    {
        if ($coors = $this->search($keyword)) {
            if ($coors->priority != 0) {
                $this->depArr[$coors->priority-1][] = $keyword;
                unset($this->depArr[$coors->priority][$coors->index]);
            }
        }
    }

    public function moveDown($keyword)
    {
        if ($coors = $this->search($keyword)) {
            if ($coors->priority != 0) {
                $this->depArr[$coors->priority+1][] = $keyword;
                unset($this->depArr[$coors->priority][$coors->index]);
            }
        }
    }

    public function search($keyword, $return_index = true)
    {
        foreach ($this->depArr as $pri => $keywords) {
            foreach ($keywords as $i => $kw) {
                if (!strcasecmp($keyword, $kw)) {
                    if ($return_index) {
                       return new DependencyArrayCoordinates($pri, $i);
                    } else {
                       return true;
                    }
                }
            }
        }

        return false;
    }

    public function asArray()
    {
        return $this->depArr;
    }
}

class DependencyArrayCoordinates {
    public $priority;
    public $index;

    public function __construct($pri, $i)
    {
        $this->priority = $pri;
        $this->index = $i;
    }
}