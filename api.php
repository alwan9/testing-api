<?php
header('Content-Type: application/json');

// Fungsi untuk membaca data dari file JSON
function getDataFromFile($file) {
    if (!file_exists($file)) {
        return ["items" => []];
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

// Fungsi untuk menyimpan data ke file JSON
function saveDataToFile($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Path ke file JSON
$file = 'data.json';

// Metode HTTP
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Ambil parameter dari URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    // Baca data dari file JSON
    $data = getDataFromFile($file);

    if ($id) {
        // Filter data berdasarkan ID
        $item = array_filter($data['items'], fn($item) => $item['id'] === $id);

        if (!empty($item)) {
            echo json_encode(array_values($item)[0]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Item not found"]);
        }
    } else {
        // Tampilkan semua data
        echo json_encode($data);
    }
} elseif ($method === 'POST') {
    // Tambahkan data baru
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name']) || !isset($input['price'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $data = getDataFromFile($file);

    // Buat ID baru (otomatis increment)
    $newId = empty($data['items']) ? 1 : end($data['items'])['id'] + 1;
    $input['id'] = $newId;

    // Tambahkan data ke array
    $data['items'][] = $input;

    // Simpan data ke file
    saveDataToFile($file, $data);

    echo json_encode(["message" => "Item added successfully", "data" => $input]);
} elseif ($method === 'PUT') {
    // Update data
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || !isset($input['name']) || !isset($input['price'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $data = getDataFromFile($file);

    // Cari item berdasarkan ID
    $index = array_search($input['id'], array_column($data['items'], 'id'));

    if ($index === false) {
        http_response_code(404);
        echo json_encode(["message" => "Item not found"]);
    } else {
        // Update data
        $data['items'][$index] = $input;

        // Simpan data ke file
        saveDataToFile($file, $data);

        echo json_encode(["message" => "Item updated successfully"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>