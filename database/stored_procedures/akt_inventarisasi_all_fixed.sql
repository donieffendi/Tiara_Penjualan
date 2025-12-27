-- Stored Procedure untuk menampilkan semua data inventarisasi tanpa filter
-- Digunakan ketika user tidak memilih cabang atau periode tertentu
-- FIXED: Format tanggal sudah diperbaiki sesuai dengan procedure asli

DELIMITER $$

DROP PROCEDURE IF EXISTS `akt_inventarisasi_all`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `akt_inventarisasi_all`(
    JNSX VARCHAR(20),
    STOKX VARCHAR(10)
)
BEGIN
    -- Variabel untuk tahun dan bulan saat ini
    SET @yerini := YEAR(NOW());
    SET @bul := LPAD(MONTH(NOW()), 2, '0');
    SET @yer := YEAR(NOW());
    SET @yeritu := '';

    -- Sub kategori Kode 3
    SET @subTS := '("151","152","153","154","155","156","157","158","159","160",
                    "161","162","163","164","165","166","167","168","169","170",
                    "171","172","173","174","175","176","177","178","179","180",
                    "200","201","202","203")';

    -- Hitung tanggal berdasarkan STOKX (sama seperti procedure asli)
    SET @tanggale := CONCAT(@yer, '-', @bul, '-01');
    SET @getTgl := IF(STOKX="AKHIR", LAST_DAY(DATE(CONCAT(@yer, '-', @bul, '-01'))), @tanggale);

    -- Ambil daftar semua cabang aktif
    SET @cabangList := (SELECT GROUP_CONCAT(CONCAT('"', KODE, '"')) FROM toko WHERE STA IN ('MA','CB','DC'));

    CASE
        WHEN JNSX='KODE3' THEN
            -- Query untuk KODE3 dari SEMUA cabang
            SET @stat_01 := CONCAT('
                SELECT hasil.* FROM (
                    SELECT
                        CBG,
                        CONCAT("STOK ", ', QUOTE(STOKX), ', " KODE 3 ", CBG) AS NO_BUKTI,
                        DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") AS TGL,
                        ini.SUB,
                        aotprice.KELOMPOK,
                        SUM(STOKTK)+SUM(STOKGD) AS SALDO,
                        SUM(totaltk)+SUM(totalgd) AS TOTAL,
                        0 AS GANTUNG,
                        0 AS RP_GANTUNG,
                        SUM(STOKTK)+SUM(STOKGD) - 0 AS SELISIH,
                        SUM(totaltk)+SUM(totalgd) - 0 AS RP_SELISIH,
                        "T-AKK2-039.5" AS NO_FORM,
                        t.NAMA_TOKO AS NA_TOKO
                    FROM (
                        SELECT
                            brgdt.CBG,
                            brg.SUB,
                            brgdt.ak', @bul, ' AS STOKTK,
                            IFNULL(brgd.ak', @bul, ', 0) AS STOKGD,
                            ROUND(brgdt.ak', @bul, ' * brgdt.harga', @bul, ') AS totaltk,
                            ROUND(IFNULL(brgd.ak', @bul, ', 0) * IFNULL(brgd.harga', @bul, ', 0)) AS totalgd
                        FROM
                            brg
                        INNER JOIN brgdt ON brg.kd_brg = brgdt.kd_brg
                        LEFT JOIN brgd ON brgdt.cbg = brgd.cbg AND brgdt.KD_BRG = brgd.KD_BRG
                        WHERE
                            brgdt.yer = "', @yer, '"
                            AND brg.SUB IN ', @subTS, '
                            AND brgdt.CBG IN (', @cabangList, ')
                    ) AS ini
                    LEFT JOIN aotprice ON ini.sub = aotprice.SUB
                    LEFT JOIN toko t ON ini.CBG = t.KODE
                    WHERE aotprice.TYPE <> "BSN"
                    GROUP BY CBG, SUB
                    ORDER BY CBG, SUB
                ) AS hasil
            ');
            PREPARE stmt FROM @stat_01;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        WHEN JNSX='SPM' THEN
            -- Query untuk SPM (Non Kode 3) dari SEMUA cabang
            SET @stat_01 := CONCAT('
                SELECT hasil.* FROM (
                    SELECT
                        CBG,
                        CONCAT("STOK ", ', QUOTE(STOKX), ', " NON KODE 3 ", CBG) AS NO_BUKTI,
                        DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") AS TGL,
                        ini.SUB,
                        aotprice.KELOMPOK,
                        SUM(STOKTK)+SUM(STOKGD) AS SALDO,
                        SUM(totaltk)+SUM(totalgd) AS TOTAL,
                        0 AS GANTUNG,
                        0 AS RP_GANTUNG,
                        SUM(STOKTK)+SUM(STOKGD) - 0 AS SELISIH,
                        SUM(totaltk)+SUM(totalgd) - 0 AS RP_SELISIH,
                        "TGZ.AKK.039.9" AS NO_FORM,
                        t.NAMA_TOKO AS NA_TOKO
                    FROM (
                        SELECT
                            brgdt.CBG,
                            brg.SUB,
                            brgdt.ak', @bul, ' AS STOKTK,
                            IFNULL(brgd.ak', @bul, ', 0) AS STOKGD,
                            ROUND(brgdt.ak', @bul, ' * brgdt.harga', @bul, ') AS totaltk,
                            ROUND(IFNULL(brgd.ak', @bul, ', 0) * IFNULL(brgd.harga', @bul, ', 0)) AS totalgd
                        FROM
                            brg
                        INNER JOIN brgdt ON brg.kd_brg = brgdt.kd_brg
                        LEFT JOIN brgd ON brgdt.cbg = brgd.cbg AND brgdt.KD_BRG = brgd.KD_BRG
                        WHERE
                            brgdt.yer = "', @yer, '"
                            AND brg.SUB NOT IN ', @subTS, '
                            AND brgdt.CBG IN (', @cabangList, ')
                    ) AS ini
                    LEFT JOIN aotprice ON ini.sub = aotprice.SUB
                    LEFT JOIN toko t ON ini.CBG = t.KODE
                    WHERE aotprice.TYPE <> "BSN"
                    GROUP BY CBG, SUB
                    ORDER BY CBG, SUB
                ) AS hasil
            ');
            PREPARE stmt FROM @stat_01;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        WHEN JNSX='BSN' THEN
            -- Query untuk BUSANA dari SEMUA cabang
            SET @stat_01 := CONCAT('
                SELECT hasil.* FROM (
                    SELECT
                        XSAA.CBG,
                        CONCAT("STOK ", ', QUOTE(STOKX), ', " BSN ", XSAA.CBG) AS NO_BUKTI,
                        DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") AS TGL,
                        XSAA.CNT AS SUB,
                        b.KELOMPOK,
                        SUM(AK) AS SALDO,
                        SUM(XSAA.AK*XSAA.HB) AS TOTAL,
                        0 AS GANTUNG,
                        0 AS RP_GANTUNG,
                        SUM(AK) - 0 AS SELISIH,
                        SUM(XSAA.AK*XSAA.HB) - 0 AS RP_SELISIH,
                        "TGZ.AKK.039.10" AS NO_FORM,
                        t.NAMA_TOKO AS NA_TOKO
                    FROM (
                        SELECT
                            B.CBG,
                            LEFT(A.CNT, 3) AS CNT,
                            B.KD_BRG,
                            A.HJUAL', @bul, ' AS HJ,
                            B.AK', @bul, ' AS AK,
                            A.HBNET', @bul, ' AS HB
                        FROM
                            brgbsn A
                        INNER JOIN brgbsnd B ON A.KD_BRG = B.KD_BRG
                        WHERE
                            LEFT(A.CNT, 3) IN (SELECT LEFT(cnt, 3) FROM cntbsn WHERE st_cnt = "P")
                            AND B.CBG IN (', @cabangList, ')
                    ) XSAA
                    LEFT JOIN aotprice b ON XSAA.CNT = b.SUB
                    LEFT JOIN toko t ON XSAA.CBG = t.KODE
                    GROUP BY XSAA.CBG, CNT
                    ORDER BY XSAA.CBG, CNT
                ) AS hasil
            ');
            PREPARE stmt FROM @stat_01;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        WHEN JNSX='PH' THEN
            -- Query untuk PUSAT HIDANGAN dari SEMUA cabang
            SET @stat_01 := CONCAT('
                SELECT hasil.* FROM (
                    SELECT
                        EE.CBG,
                        CONCAT("STOK ", ', QUOTE(STOKX), ', " PH ", EE.CBG) AS NO_BUKTI,
                        DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") AS TGL,
                        EE.SUB,
                        aotfc.KELOMPOK,
                        SUM(AK) AS SALDO,
                        SUM(TOTAL_T) AS TOTAL,
                        0 AS GANTUNG,
                        0 AS RP_GANTUNG,
                        SUM(AK) - 0 AS SELISIH,
                        SUM(TOTAL_T) - 0 AS RP_SELISIH,
                        "TGZ.AKK.039.11" AS NO_FORM,
                        t.NAMA_TOKO AS NA_TOKO
                    FROM (
                        SELECT
                            b.CBG,
                            a.SUB,
                            b.AK', @bul, ' AS AK,
                            ROUND(b.AK', @bul, ' * b.HARGA', @bul, ') AS TOTAL_T
                        FROM
                            brgfcd b
                        INNER JOIN brgfc a ON b.KD_BRG = a.KD_BRG
                        WHERE
                            b.CBG IN (', @cabangList, ')
                    ) AS EE
                    LEFT JOIN aotfc ON EE.sub = aotfc.SUB
                    LEFT JOIN toko t ON EE.CBG = t.KODE
                    WHERE aotfc.TYPE = "JL"
                    GROUP BY EE.CBG, EE.SUB
                    ORDER BY EE.CBG, EE.SUB
                ) AS hasil
            ');
            PREPARE stmt FROM @stat_01;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        ELSE
            SELECT NULL;
    END CASE;

END$$

DELIMITER ;

-- Cara penggunaan:
-- CALL akt_inventarisasi_all('KODE3', 'AKHIR');  -- Semua cabang Kode 3, tanggal akhir bulan
-- CALL akt_inventarisasi_all('SPM', 'AWAL');     -- Semua cabang Non Kode 3, tanggal 1
-- CALL akt_inventarisasi_all('BSN', 'AKHIR');    -- Semua cabang Busana, tanggal akhir bulan
-- CALL akt_inventarisasi_all('PH', 'AWAL');      -- Semua cabang Pusat Hidangan, tanggal 1

-- PERBAIKAN:
-- 1. Tanggal sekarang menggunakan @getTgl yang dihitung berdasarkan STOKX
--    - AWAL = tanggal 1 bulan ini
--    - AKHIR = tanggal terakhir bulan ini
-- 2. Format tanggal menggunakan DATE_FORMAT dengan QUOTE untuk konsistensi
-- 3. @bul sudah di-LPAD untuk memastikan format 2 digit (01, 02, dst)
