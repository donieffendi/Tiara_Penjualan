-- Stored Procedure untuk menampilkan semua data inventarisasi tanpa filter
-- FIXED VERSION: Menggunakan struktur yang sama dengan procedure asli
-- Perbaikan: Format tanggal dan handling collation

DELIMITER $$

DROP PROCEDURE IF EXISTS `akt_inventarisasi_all`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `akt_inventarisasi_all`(
    JNSX VARCHAR(20),
    STOKX VARCHAR(10)
)
BEGIN
    -- Set collation untuk menghindari masalah
    SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci';

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

    -- Temporary table untuk hasil
    DROP TEMPORARY TABLE IF EXISTS temp_hasil;
    CREATE TEMPORARY TABLE temp_hasil (
        CBG VARCHAR(10),
        NO_BUKTI VARCHAR(100),
        TGL VARCHAR(20),
        SUB VARCHAR(10),
        KELOMPOK VARCHAR(100),
        SALDO DECIMAL(20,3),
        TOTAL DECIMAL(20,2),
        GANTUNG DECIMAL(20,3),
        RP_GANTUNG DECIMAL(20,2),
        SELISIH DECIMAL(20,3),
        RP_SELISIH DECIMAL(20,2),
        NO_FORM VARCHAR(50),
        NA_TOKO VARCHAR(100)
    );

    -- Ambil daftar semua cabang aktif
    BEGIN
        DECLARE done INT DEFAULT FALSE;
        DECLARE v_cbg VARCHAR(10);
        DECLARE cur_cbg CURSOR FOR SELECT KODE FROM toko WHERE STA IN ('MA','CB','DC');
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

        OPEN cur_cbg;

        read_loop: LOOP
            FETCH cur_cbg INTO v_cbg;
            IF done THEN
                LEAVE read_loop;
            END IF;

            -- Cek apakah database cabang ada
            SET @db_check := (SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = v_cbg);
            IF @db_check = 0 THEN
                ITERATE read_loop;
            END IF;

            -- Set nama toko
            SET @namtoko := (SELECT NAMA_TOKO FROM toko WHERE KODE = v_cbg LIMIT 1);

            CASE
                WHEN JNSX='KODE3' THEN
                    SET @stat_01 := CONCAT('
                        INSERT INTO temp_hasil
                        SELECT "', v_cbg, '" as CBG,
                               CONCAT("STOK ", ', QUOTE(STOKX), ', " KODE 3 ", "', v_cbg, '") as NO_BUKTI,
                               DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") as TGL,
                               ini.SUB, aotprice.KELOMPOK,
                               SUM(STOKTK)+SUM(STOKGD) as SALDO,
                               SUM(totaltk)+SUM(totalgd) as TOTAL,
                               0 as GANTUNG, 0 as RP_GANTUNG,
                               SUM(STOKTK)+SUM(STOKGD) - 0 as SELISIH,
                               SUM(totaltk)+SUM(totalgd) - 0 as RP_SELISIH,
                               "T-AKK2-039.5" as NO_FORM,
                               ', QUOTE(@namtoko), ' as NA_TOKO
                        FROM (
                            SELECT brgdt.CBG, brg.SUB,
                                   brgdt.ak', @bul, ' as STOKTK,
                                   IFNULL(brgd.ak', @bul, ', 0) as STOKGD,
                                   ROUND(brgdt.ak', @bul, ' * brgdt.harga', @bul, ') as totaltk,
                                   ROUND(IFNULL(brgd.ak', @bul, ', 0) * IFNULL(brgd.harga', @bul, ', 0)) as totalgd
                            FROM ', v_cbg, '.brg, ', v_cbg, '.brgdt', @yeritu, ' as brgdt
                            LEFT JOIN ', v_cbg, '.brgd', @yeritu, ' as brgd
                                ON brgdt.cbg = brgd.cbg AND brgdt.KD_BRG = brgd.KD_BRG AND brgd.yer = "', @yer, '"
                            WHERE brg.kd_brg = brgdt.kd_brg
                                AND brgdt.cbg = "', v_cbg, '"
                                AND brgdt.yer = "', @yer, '"
                                AND brg.SUB IN ', @subTS, '
                        ) as ini
                        LEFT JOIN ', v_cbg, '.aotprice ON ini.sub = aotprice.SUB
                        WHERE aotprice.TYPE <> "BSN"
                        GROUP BY SUB
                        ORDER BY SUB
                    ');
                    PREPARE stmt FROM @stat_01;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;

                WHEN JNSX='SPM' THEN
                    SET @stat_01 := CONCAT('
                        INSERT INTO temp_hasil
                        SELECT "', v_cbg, '" as CBG,
                               CONCAT("STOK ", ', QUOTE(STOKX), ', " NON KODE 3 ", "', v_cbg, '") as NO_BUKTI,
                               DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") as TGL,
                               ini.SUB, aotprice.KELOMPOK,
                               SUM(STOKTK)+SUM(STOKGD) as SALDO,
                               SUM(totaltk)+SUM(totalgd) as TOTAL,
                               0 as GANTUNG, 0 as RP_GANTUNG,
                               SUM(STOKTK)+SUM(STOKGD) - 0 as SELISIH,
                               SUM(totaltk)+SUM(totalgd) - 0 as RP_SELISIH,
                               "TGZ.AKK.039.9" as NO_FORM,
                               ', QUOTE(@namtoko), ' as NA_TOKO
                        FROM (
                            SELECT brgdt.CBG, brg.SUB,
                                   brgdt.ak', @bul, ' as STOKTK,
                                   IFNULL(brgd.ak', @bul, ', 0) as STOKGD,
                                   ROUND(brgdt.ak', @bul, ' * brgdt.harga', @bul, ') as totaltk,
                                   ROUND(IFNULL(brgd.ak', @bul, ', 0) * IFNULL(brgd.harga', @bul, ', 0)) as totalgd
                            FROM ', v_cbg, '.brg, ', v_cbg, '.brgdt', @yeritu, ' as brgdt
                            LEFT JOIN ', v_cbg, '.brgd', @yeritu, ' as brgd
                                ON brgdt.cbg = brgd.cbg AND brgdt.KD_BRG = brgd.KD_BRG AND brgd.yer = "', @yer, '"
                            WHERE brg.kd_brg = brgdt.kd_brg
                                AND brgdt.cbg = "', v_cbg, '"
                                AND brgdt.yer = "', @yer, '"
                                AND brg.SUB NOT IN ', @subTS, '
                        ) as ini
                        LEFT JOIN ', v_cbg, '.aotprice ON ini.sub = aotprice.SUB
                        WHERE aotprice.TYPE <> "BSN"
                        GROUP BY SUB
                        ORDER BY SUB
                    ');
                    PREPARE stmt FROM @stat_01;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;

                WHEN JNSX='BSN' THEN
                    SET @stat_01 := CONCAT('
                        INSERT INTO temp_hasil
                        SELECT "', v_cbg, '" as CBG,
                               CONCAT("STOK ", ', QUOTE(STOKX), ', " BSN ", "', v_cbg, '") as NO_BUKTI,
                               DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") as TGL,
                               XSAA.CNT as SUB, b.KELOMPOK,
                               SUM(AK) as SALDO,
                               SUM(XSAA.AK*XSAA.HB) as TOTAL,
                               0 as GANTUNG, 0 as RP_GANTUNG,
                               SUM(AK) - 0 as SELISIH,
                               SUM(XSAA.AK*XSAA.HB) - 0 as RP_SELISIH,
                               "TGZ.AKK.039.10" as NO_FORM,
                               ', QUOTE(@namtoko), ' as NA_TOKO
                        FROM (
                            SELECT LEFT(CNT, 3) CNT, B.KD_BRG,
                                   A.HJUAL', @bul, ' HJ,
                                   B.AK', @bul, ' AK,
                                   A.HBNET', @bul, ' HB
                            FROM brgbsn A, brgbsnd', @yeritu, ' B
                            WHERE A.KD_BRG = B.KD_BRG
                                AND B.CBG = "', v_cbg, '"
                                AND LEFT(A.CNT, 3) IN (SELECT LEFT(cnt, 3) FROM cntbsn WHERE st_cnt = "P")
                        ) XSAA
                        LEFT JOIN ', v_cbg, '.aotprice b ON CNT = b.SUB
                        GROUP BY CNT
                        ORDER BY CNT
                    ');
                    PREPARE stmt FROM @stat_01;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;

                WHEN JNSX='PH' THEN
                    IF v_cbg = 'TMM' THEN
                        -- TMM case handled separately
                        INSERT INTO temp_hasil VALUES (
                            v_cbg,
                            CONCAT("STOK ", STOKX, " PH ", v_cbg),
                            DATE_FORMAT(@getTgl, "%d/%m/%Y"),
                            "-", "-", 0, 0, 0, 0, 0, 0,
                            "TGZ.AKK.039.11",
                            "TIARA MONANG MANING"
                        );
                    ELSE
                        SET @stat_01 := CONCAT('
                            INSERT INTO temp_hasil
                            SELECT "', v_cbg, '" as CBG,
                                   CONCAT("STOK ", ', QUOTE(STOKX), ', " PH ", "', v_cbg, '") as NO_BUKTI,
                                   DATE_FORMAT(', QUOTE(@getTgl), ', "%d/%m/%Y") as TGL,
                                   EE.SUB, aotfc.KELOMPOK,
                                   SUM(AK) as SALDO,
                                   SUM(TOTAL_T) AS TOTAL,
                                   0 as GANTUNG, 0 as RP_GANTUNG,
                                   SUM(AK) - 0 as SELISIH,
                                   SUM(TOTAL_T) - 0 as RP_SELISIH,
                                   "TGZ.AKK.039.11" as NO_FORM,
                                   ', QUOTE(@namtoko), ' as NA_TOKO
                            FROM (
                                SELECT b.CBG, a.SUB,
                                       b.AK', @bul, ' as AK,
                                       ROUND(b.AK', @bul, ' * b.HARGA', @bul, ') as TOTAL_T
                                FROM ', v_cbg, '.brgfcd', @yeritu, ' b, ', v_cbg, '.brgfc a
                                WHERE b.KD_BRG = a.KD_BRG AND b.CBG = "', v_cbg, '"
                            ) AS EE
                            LEFT JOIN ', v_cbg, '.aotfc ON EE.sub = aotfc.SUB
                            WHERE aotfc.TYPE = "JL"
                            GROUP BY EE.SUB
                            ORDER BY EE.SUB
                        ');
                        PREPARE stmt FROM @stat_01;
                        EXECUTE stmt;
                        DEALLOCATE PREPARE stmt;
                    END IF;
            END CASE;

        END LOOP;

        CLOSE cur_cbg;
    END;

    -- Return hasil
    SELECT * FROM temp_hasil ORDER BY CBG, SUB;

    -- Cleanup
    DROP TEMPORARY TABLE IF EXISTS temp_hasil;

END$$

DELIMITER ;

-- Cara penggunaan:
-- CALL akt_inventarisasi_all('KODE3', 'AKHIR');  -- Semua cabang Kode 3, tanggal akhir bulan
-- CALL akt_inventarisasi_all('SPM', 'AWAL');     -- Semua cabang Non Kode 3, tanggal 1
-- CALL akt_inventarisasi_all('BSN', 'AKHIR');    -- Semua cabang Busana, tanggal akhir bulan
-- CALL akt_inventarisasi_all('PH', 'AWAL');      -- Semua cabang Pusat Hidangan, tanggal 1

-- PERBAIKAN v2:
-- 1. Menggunakan CURSOR untuk loop setiap cabang (seperti procedure asli per cabang)
-- 2. Menggunakan database prefix per cabang (TGZ.brg, TGZ.brgdt, dll)
-- 3. Format tanggal dengan DATE_FORMAT dan QUOTE untuk konsistensi
-- 4. Temporary table untuk menghindari masalah collation
-- 5. Hasil akhir di-ORDER BY CBG dan SUB
