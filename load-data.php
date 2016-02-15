<?php
// memanggil file config.php
require 'config.php';

// koneksi ke database
$database = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($database->connect_error) {
    die('Oops!! database Not Connect : ' . $database->connect_error);
}

// Alternative SQL join in Datatables
$id_table = 'id_karyawan';

$columns = array(
             'first_name',
             'last_name',
             'position_name',
             'office',
             'start_date'
           );

// gunakan join disini
$from = 'karyawan K INNER JOIN position P ON K.position = P.id_position';

$id_table = $id_table != '' ? $id_table . ',' : '';
// custom SQL
$sql = "SELECT {$id_table} ".implode(',', $columns)." FROM {$from}";

// search
if (isset($_GET['search']['value']) && $_GET['search']['value'] != '') {
    $search = $_GET['search']['value'];
    $where  = '';
    // create parameter pencarian kesemua kolom yang tertulis
    // di $columns
    for ($i=0; $i < count($columns); $i++) {
        $where .= $columns[$i] . ' LIKE "%'.$search.'%"';

        // agar tidak menambahkan 'OR' diakhir Looping
        if ($i < count($columns)-1) {
            $where .= ' OR ';
        }
    }

    $sql .= ' WHERE ' . $where;
}

//SORT Kolom
$sortColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 0;
$sortDir    = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'asc';

$sortColumn = $columns[$sortColumn];

$sql .= " ORDER BY {$sortColumn} {$sortDir}";

// var_dump($sql);
$count = $database->query($sql);
// hitung semua data
$totaldata = $count->num_rows;

$count->close();

// memberi Limit
$start  = isset($_GET['start']) ? $_GET['start'] : 0;
$length = isset($_GET['length']) ? $_GET['length'] : 10;


$sql .= " LIMIT {$start}, {$length}";

$data  = $database->query($sql);

// create json format
$datatable['draw']            = isset($_GET['draw']) ? $_GET['draw'] : 1;
$datatable['recordsTotal']    = $totaldata;
$datatable['recordsFiltered'] = $totaldata;
$datatable['data']            = array();

while ($row = $data->fetch_assoc()) {

    $fields = array();
    for ($i=0; $i < count($columns); $i++) {
        # code...
        $fields[] = $row["{$columns[$i]}"];
    }
    

    $datatable['data'][] = $fields;
}

$data->close();
echo json_encode($datatable);
