<?php

trait CustomTraitAbsen
{

	private static function resizeAndCompressImage(
		string $tmpPath,
		string $mime,
		int $maxWidth = 1920,
		int $quality = 82
	): string {

		[$width, $height] = getimagesize($tmpPath);

		if ($width <= $maxWidth) {
			return $tmpPath; // tidak perlu resize
		}

		$ratio = $height / $width;
		$newWidth  = $maxWidth;
		$newHeight = (int) ($maxWidth * $ratio);

		$src = match ($mime) {
			'image/jpeg' => imagecreatefromjpeg($tmpPath),
			'image/png'  => imagecreatefrompng($tmpPath),
			'image/webp' => imagecreatefromwebp($tmpPath),
			default => throw new Exception('Tipe gambar tidak didukung'),
		};

		$dst = imagecreatetruecolor($newWidth, $newHeight);

		// transparansi PNG
		if ($mime === 'image/png') {
			imagealphablending($dst, false);
			imagesavealpha($dst, true);
		}

		imagecopyresampled(
			$dst,
			$src,
			0,
			0,
			0,
			0,
			$newWidth,
			$newHeight,
			$width,
			$height
		);

		$tmpNew = tempnam(sys_get_temp_dir(), 'img_');

		match ($mime) {
			'image/jpeg' => imagejpeg($dst, $tmpNew, $quality),
			'image/png'  => imagepng($dst, $tmpNew, 7),
			'image/webp' => imagewebp($dst, $tmpNew, $quality),
		};

		// PHP 8.3: biarkan GC handle GdImage
		$src = null;
		$dst = null;

		return $tmpNew;
	}

	public static function uploadFileAbsen(
		string $api_key = '',
		string $path = '',
		array $file = array(),
		array $ext = array(),
		int $maxSize = 2097152, // 2MB
		string $nama_file = ''
	) {
		try {
			if (empty($api_key) || $api_key !== get_option(ABSEN_APIKEY)) {
				throw new Exception('Api key tidak ditemukan');
			}

			if (empty($file)) {
				throw new Exception('Oops, file belum dipilih');
			}

			if (empty($ext)) {
				throw new Exception('Extensi file belum ditentukan ' . json_encode($file));
			}

			if (empty($path)) {
				throw new Exception('Lokasi folder belum ditentukan ' . json_encode($file));
			}

			// hard limit 15MB
			$hardLimit = 15 * 1024 * 1024; // 15MB
			if ($file['size'] > $hardLimit) {
				throw new Exception('File terlalu besar, maksimal 15MB');
			}

			$imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			if (!in_array($imageFileType, $ext)) {
				throw new Exception('Lampiran wajib ber-type ' . implode(', ', $ext));
			}

			// ====== IMAGE FRIENDLY FLOW ======
			if (str_starts_with($file['type'], 'image/')) {

				$processedTmp = self::resizeAndCompressImage(
					$file['tmp_name'],
					$file['type']
				);

				// validasi SETELAH resize
				if (filesize($processedTmp) > $maxSize) {
					throw new Exception('Ukuran file melebihi 2MB setelah dikompresi');
				}

				$file['tmp_name'] = $processedTmp;
			} else {
				// non-image tetap divalidasi langsung
				if ($file['size'] > $maxSize) {
					throw new Exception('Ukuran file melebihi ukuran maksimal');
				}
			}

			// ====== NAMA FILE ======
			if (!empty($nama_file)) {
				$file['name'] = $nama_file . '.' . $imageFileType;
			} else {
				$file['name'] = date('Y-m-d-H-i-s') . '-' . $file['name'];
			}

			$target = $path . $file['name'];

			$moved = rename($file['tmp_name'], $target);

			if ($moved) {
				return [
					'status'   => true,
					'filename' => $file['name']
				];
			}

			throw new Exception('Oops, gagal upload file ' . $file['name']);
		} catch (Exception $e) {
			return [
				'status'  => false,
				'message' => $e->getMessage()
			];
		}
	}
}
