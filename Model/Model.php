<?php
/**
 * Created by Jordan Lovato.
 */
/** TODO: Extend/Move code such that there is a normal model and a wordpress model. */
abstract class Model {
    protected static $init = false;
    protected static $count = 0;

    protected $table_name = "";
    protected $prefix = "fbbc_";
    protected $select = array();
    protected $orderby = array();
    protected $order = "ASC";
    protected $cols;

    const IS_WORDPRESS = false;

    const DB_NAME = "webapps";
    const DB_HOST = "localhost";
    const DB_USERNAME = DB_USERNAME;
    const DB_PASSWORD = DB_PASSWORD;

    public function __construct()
    {
        $this->init_db();

        // Set the default table name var
        if (self::IS_WORDPRESS) {
            global $wpdb;
            $this->prefix = $wpdb->prefix;
        }
        $this->table_name = $this->prefix . 'table_' . self::$count;

        self::$count++;
    }

    public function insert($data, $data_format = array())
    {
        if ($this->validate($data)) {
            if (self::IS_WORDPRESS) {
                global $wpdb;
                if (count(date) === count($data_format))
                    $wpdb->insert($this->get_table_name(), $data, $data_format);
                else
                    throw new Exception("Mismatch between data given to Model::insert() and data_format");
            } else {
                $sql = "";
                $insert_string = "INSERT INTO " . $this->get_table_name();

                // Create the columns string
                $col_string = "(";
                foreach ($data[0] as $col) {
                    $col_string .= "$col, ";
                }
                $col_string = substr($col_string, 0, -2) . ") ";

                // Create the values string
                $value_string = "VALUES";
                foreach ($data as $row) {
                    $single_row_string = "(";
                    foreach ($row as $col_name => $col_val) {
                        $single_row_string .= "$col_val, ";
                    }
                    $single_row_string = substr($single_row_string, 0, -2) . ")";
                    $value_string .= $single_row_string . ", ";
                }
                $value_string = substr($value_string, 0, -2);

                // Insert into DB
                $sql = $insert_string . $col_string . $value_string;

                global $mysqli;
                $mysqli->query($sql);
            }
        }
    }

    public function delete($where)
    {
        $sql = "DELETE FROM " . $this->get_table_name() . " WHERE $where";

        if (self::IS_WORDPRESS) {
            global $wpdb;
            $wpdb->query($sql);
        } else {
            global $mysqli;
            $mysqli->query($sql);
        }
    }

    public function get($what, $where = "")
    {
        // Build query strings, or return simple data
        switch ($what) {
            case "table_name":
            case "table":
                return $this->table_name;
                break;
            case "cols":
                return $this->cols;
                break;
            case "orderby":
            case "order":
                $orderby = "";
                if (!empty($this->orderby)) {
                    $orderby = "ORDER BY ";
                    foreach ($this->orderby as $col) {
                        $orderby .= $col . ", ";
                    }
                }
                $orderby = substr($orderby, 0, -2) . " ";
                return $orderby . $this->order;
                break;
            case "select":
                if (!empty($this->select)) {
                    $select = "(";
                    foreach ($this->select as $col => $alias) {
                        if (!is_int($col)) {
                            $select .= $col . " AS " . $alias . ", ";
                        } else {
                            $select .= $alias . ", ";
                        }
                    }
                    $select = substr($select, 0, -2) . ")";
                    return $select;
                } else
                    return "(*)";
                break;
            case "row":
            case "rows":
                $sql = "SELECT " . $this->get('select') . " FROM " . $this->get('table') .
                    " WHERE " . $where . " ". $this->get('orderby');
                break;
            default:
                $sql = "SELECT " . $this->get('select') . " FROM " . $this->get('table') .
                    " " . $this->get('orderby');
                break;
        }

        // Fetch data from DB and Parse it
        if (self::IS_WORDPRESS) {
            global $wpdb;
            $select_data = $wpdb->get_results($sql, 'ARRAY_A');
        } else {
            global $mysqli;
            $select_data = $mysqli->query($sql, MYSQLI_USE_RESULT);
            $select_data = $this->fetch_data($select_data);
        }

        return $select_data;
    }

    private function fetch_data(mysqli_result $result)
    {
        $fetched_data = $result->fetch_all(MYSQLI_BOTH);
        mysqli_free_result($result);
        return $fetched_data;
    }

    public function set_select($cols)
    {
        $this->select = $cols;
    }

    public function set_order($by, $order = "ASC")
    {
        if (!is_array($by)) $by = array($by);
        $this->orderby = $by;
        $this->order = $order;
    }

    private function init_db()
    {
        // If this is wordpress, the db is all ready to go
        if (!self::IS_WORDPRESS && !isset($GLOBALS['mysqli'])) {
            $mysqli = new mysqli(self::DB_HOST, self::DB_NAME, self::DB_USERNAME, self::DB_PASSWORD);
            if (!mysqli_connect_error()) {
                $GLOBALS['mysqli'] = $mysqli;
            }
        }
    }

    public function __destruct()
    {
        // If this is wordpress, we don't need to close the db
        if (!self::IS_WORDPRESS) {
            global $mysqli;
            $mysqli->close();
        }
    }

    public function get_table_name()
    {
        return $this->get('table');
    }

    abstract function validate();
    abstract function create_table();
}