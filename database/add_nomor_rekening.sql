-- Migration: Add nomor_rekening column to penjualan table
-- Run this SQL in your database to add the nomor_rekening field

ALTER TABLE `penjualan` 
ADD COLUMN `nomor_rekening` VARCHAR(50) NULL 
AFTER `metode_transaksi`;

