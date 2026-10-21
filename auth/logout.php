<?php
require_once __DIR__ . '/../config/config.php';

keluar();
session_start();               // sesi baru hanya untuk menyimpan notifikasi
notif('info', 'Anda sudah keluar.');
redirect('index.php');
