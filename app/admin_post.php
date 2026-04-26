<?php

function admin_form_redirect_target(string $default = 'admin.php'): string
{
    $target = trim((string) ($_POST['redirect_to'] ?? ''));
    if ($target === '') {
        return $default;
    }

    return normalize_next_path($target, $default);
}

function admin_redirect_with_status(string $status, string $target = 'admin.php'): void
{
    $separator = str_contains($target, '?') ? '&' : '?';
    header('Location: ' . $target . $separator . 'ok=' . rawurlencode($status));
    exit;
}

function admin_handle_delete(mysqli $mysqli, array &$errors): void
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        $errors[] = 'NeplatnĂ© ID pro smazĂˇnĂ­.';
        return;
    }

    $imageToDelete = '';
    $stmtImage = $mysqli->prepare('SELECT image_url FROM bikes WHERE id = ? LIMIT 1');
    if ($stmtImage) {
        $stmtImage->bind_param('i', $id);
        $stmtImage->execute();
        $resultImage = $stmtImage->get_result();
        $rowImage = $resultImage ? $resultImage->fetch_assoc() : null;
        $stmtImage->close();
        $imageToDelete = (string) ($rowImage['image_url'] ?? '');
    }

    $stmt = $mysqli->prepare('DELETE FROM bikes WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            write_audit_log($mysqli, 'delete', 'bike', $id);
            if ($imageToDelete !== '' && is_local_bike_image($imageToDelete)) {
                $absolutePath = local_bike_image_absolute_path($imageToDelete);
                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }
            admin_redirect_with_status('deleted');
        }
        $stmt->close();
    }

    $errors[] = 'SmazĂˇnĂ­ se nepodaĹ™ilo provĂ©st.';
}

function admin_handle_duplicate(mysqli $mysqli, array &$errors): void
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        $errors[] = 'NeplatnĂ© ID pro kopĂ­rovĂˇnĂ­.';
        return;
    }

    $stmtSource = $mysqli->prepare('SELECT name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock FROM bikes WHERE id = ? LIMIT 1');
    if (!$stmtSource) {
        $errors[] = 'KopĂ­rovĂˇnĂ­ se nepodaĹ™ilo pĹ™ipravit.';
        return;
    }

    $stmtSource->bind_param('i', $id);
    $stmtSource->execute();
    $resultSource = $stmtSource->get_result();
    $sourceBike = $resultSource ? $resultSource->fetch_assoc() : null;
    $stmtSource->close();

    if (!$sourceBike) {
        $errors[] = 'Kolo pro kopĂ­rovĂˇnĂ­ nebylo nalezeno.';
        return;
    }

    $copyName = (string) $sourceBike['name'] . ' (kopie)';
    $stmtInsert = $mysqli->prepare('INSERT INTO bikes (name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock) VALUES (?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmtInsert) {
        $errors[] = 'KopĂ­rovĂˇnĂ­ se nepodaĹ™ilo provĂ©st.';
        return;
    }

    $priceCzk = (float) $sourceBike['price_czk'];
    $category = (string) $sourceBike['category'];
    $bikeType = (string) ($sourceBike['bike_type'] ?? 'uni');
    $manufacturer = (string) ($sourceBike['manufacturer'] ?? '');
    $oldPriceCzk = ($sourceBike['old_price_czk'] === null || $sourceBike['old_price_czk'] === '') ? '' : (string) $sourceBike['old_price_czk'];
    $isNew = (int) ($sourceBike['is_new'] ?? 0);
    $description = (string) $sourceBike['description'];
    $imageUrl = (string) ($sourceBike['image_url'] ?? '');
    $color = (string) ($sourceBike['color'] ?? '');
    $frameSize = (string) ($sourceBike['frame_size'] ?? '');
    $wheelSize = (string) ($sourceBike['wheel_size'] ?? '');
    $weightKg = ($sourceBike['weight_kg'] === null || $sourceBike['weight_kg'] === '') ? '' : (string) $sourceBike['weight_kg'];
    $tires = (string) ($sourceBike['tires'] ?? '');
    $lighting = (string) ($sourceBike['lighting'] ?? '');
    $brakes = (string) ($sourceBike['brakes'] ?? '');
    $bottomBracket = (string) ($sourceBike['bottom_bracket'] ?? '');
    $frontHub = (string) ($sourceBike['front_hub'] ?? '');
    $rearHub = (string) ($sourceBike['rear_hub'] ?? '');
    $frontRotor = (string) ($sourceBike['front_rotor'] ?? '');
    $rearRotor = (string) ($sourceBike['rear_rotor'] ?? '');
    $frontDerailleur = (string) ($sourceBike['front_derailleur'] ?? '');
    $rearDerailleur = (string) ($sourceBike['rear_derailleur'] ?? '');
    $wheels = (string) ($sourceBike['wheels'] ?? '');
    $battery = (string) ($sourceBike['battery'] ?? '');
    $motor = (string) ($sourceBike['motor'] ?? '');
    $displayName = (string) ($sourceBike['display_name'] ?? '');
    $saddle = (string) ($sourceBike['saddle'] ?? '');
    $cassette = (string) ($sourceBike['cassette'] ?? '');
    $frameSpec = (string) ($sourceBike['frame_spec'] ?? '');
    $forkSpec = (string) ($sourceBike['fork_spec'] ?? '');
    $chainSpec = (string) ($sourceBike['chain_spec'] ?? '');
    $note = (string) ($sourceBike['note'] ?? '');
    $inStock = (int) $sourceBike['in_stock'];

    $duplicateTypes = 'ssssdsi' . str_repeat('s', 26) . 'i';
    $stmtInsert->bind_param($duplicateTypes, $copyName, $category, $bikeType, $manufacturer, $priceCzk, $oldPriceCzk, $isNew, $description, $imageUrl, $color, $frameSize, $wheelSize, $weightKg, $tires, $lighting, $brakes, $bottomBracket, $frontHub, $rearHub, $frontRotor, $rearRotor, $frontDerailleur, $rearDerailleur, $wheels, $battery, $motor, $displayName, $saddle, $cassette, $frameSpec, $forkSpec, $chainSpec, $note, $inStock);
    if ($stmtInsert->execute()) {
        $newBikeId = (int) $mysqli->insert_id;
        $stmtInsert->close();
        write_audit_log($mysqli, 'duplicate', 'bike', $newBikeId, [
            'source_id' => $id,
            'name' => $copyName,
        ]);
        admin_redirect_with_status('duplicated');
    }

    $stmtInsert->close();
    $errors[] = 'KopĂ­rovĂˇnĂ­ se nepodaĹ™ilo provĂ©st.';
}

function admin_handle_add_notices(mysqli $mysqli, array &$errors, string &$noticeBulkText, int &$noticeBulkActive): void
{
    $noticeBulkText = trim((string) ($_POST['notice_bulk_text'] ?? ''));
    $noticeBulkActive = isset($_POST['notice_bulk_active']) ? 1 : 0;
    $rows = preg_split('/\r\n|\r|\n/', $noticeBulkText) ?: [];
    $messages = [];

    foreach ($rows as $row) {
        $message = trim((string) $row);
        if ($message !== '') {
            $messages[] = $message;
        }
    }

    if ($messages === []) {
        $errors[] = 'Zadej alespoĹ jednu dĹŻleĹľitou informaci.';
        return;
    }

    $username = current_username();
    $stmtNotice = $mysqli->prepare('INSERT INTO site_notice (message, is_active, updated_by) VALUES (?, ?, ?)');
    if (!$stmtNotice) {
        $errors[] = 'DĹŻleĹľitĂ© informace se nepodaĹ™ilo uloĹľit.';
        return;
    }

    $noticeInsertFailed = false;
    foreach ($messages as $message) {
        $stmtNotice->bind_param('sis', $message, $noticeBulkActive, $username);
        if (!$stmtNotice->execute()) {
            $noticeInsertFailed = true;
            break;
        }
        $newNoticeId = (int) $mysqli->insert_id;
        write_audit_log($mysqli, 'create', 'site_notice', $newNoticeId, [
            'is_active' => $noticeBulkActive,
        ]);
    }
    $stmtNotice->close();

    if (!$noticeInsertFailed) {
        admin_redirect_with_status('notice_added');
    }

    $errors[] = 'DĹŻleĹľitĂ© informace se nepodaĹ™ilo uloĹľit.';
}

function admin_handle_toggle_notice(mysqli $mysqli, array &$errors): void
{
    $noticeId = (int) ($_POST['notice_id'] ?? 0);
    $nextState = (int) ($_POST['next_state'] ?? 0);
    if ($noticeId <= 0) {
        $errors[] = 'NeplatnĂ© ID informace.';
        return;
    }

    $username = current_username();
    $stmtToggle = $mysqli->prepare('UPDATE site_notice SET is_active = ?, updated_by = ? WHERE id = ?');
    if ($stmtToggle) {
        $stmtToggle->bind_param('isi', $nextState, $username, $noticeId);
        if ($stmtToggle->execute()) {
            $stmtToggle->close();
            write_audit_log($mysqli, 'update', 'site_notice', $noticeId, [
                'is_active' => $nextState,
            ]);
            admin_redirect_with_status('notice_toggled');
        }
        $stmtToggle->close();
    }

    $errors[] = 'NepodaĹ™ilo se zmÄ›nit stav informace.';
}

function admin_handle_delete_notice(mysqli $mysqli, array &$errors): void
{
    $noticeId = (int) ($_POST['notice_id'] ?? 0);
    if ($noticeId <= 0) {
        $errors[] = 'NeplatnĂ© ID informace.';
        return;
    }

    $stmtDeleteNotice = $mysqli->prepare('DELETE FROM site_notice WHERE id = ?');
    if ($stmtDeleteNotice) {
        $stmtDeleteNotice->bind_param('i', $noticeId);
        if ($stmtDeleteNotice->execute()) {
            $stmtDeleteNotice->close();
            write_audit_log($mysqli, 'delete', 'site_notice', $noticeId);
            admin_redirect_with_status('notice_deleted');
        }
        $stmtDeleteNotice->close();
    }

    $errors[] = 'Informaci se nepodaĹ™ilo smazat.';
}

function admin_populate_form_from_post(array &$form): bool
{
    $form['id'] = isset($_POST['id']) ? (int) $_POST['id'] : null;
    $form['name'] = trim((string) ($_POST['name'] ?? ''));
    $form['category'] = trim((string) ($_POST['category'] ?? ''));
    $form['bike_type'] = trim((string) ($_POST['bike_type'] ?? 'uni'));
    $form['manufacturer'] = trim((string) ($_POST['manufacturer'] ?? ''));
    $form['price_czk'] = trim((string) ($_POST['price_czk'] ?? ''));
    $form['old_price_czk'] = trim((string) ($_POST['old_price_czk'] ?? ''));
    $form['is_new'] = isset($_POST['is_new']) ? 1 : 0;
    $form['description'] = trim((string) ($_POST['description'] ?? ''));
    $form['image_url'] = trim((string) ($_POST['existing_image_url'] ?? ''));
    $form['color'] = trim((string) ($_POST['color'] ?? ''));
    $form['frame_size'] = normalize_multi_option_value((string) ($_POST['frame_size'] ?? ''));
    $form['wheel_size'] = normalize_multi_option_value((string) ($_POST['wheel_size'] ?? ''));
    $form['weight_kg'] = trim((string) ($_POST['weight_kg'] ?? ''));
    $form['tires'] = trim((string) ($_POST['tires'] ?? ''));
    $form['lighting'] = trim((string) ($_POST['lighting'] ?? ''));
    $form['brakes'] = trim((string) ($_POST['brakes'] ?? ''));
    $form['bottom_bracket'] = trim((string) ($_POST['bottom_bracket'] ?? ''));
    $form['front_hub'] = trim((string) ($_POST['front_hub'] ?? ''));
    $form['rear_hub'] = trim((string) ($_POST['rear_hub'] ?? ''));
    $form['front_rotor'] = trim((string) ($_POST['front_rotor'] ?? ''));
    $form['rear_rotor'] = trim((string) ($_POST['rear_rotor'] ?? ''));
    $form['front_derailleur'] = trim((string) ($_POST['front_derailleur'] ?? ''));
    $form['rear_derailleur'] = trim((string) ($_POST['rear_derailleur'] ?? ''));
    $form['wheels'] = trim((string) ($_POST['wheels'] ?? ''));
    $form['battery'] = trim((string) ($_POST['battery'] ?? ''));
    $form['motor'] = trim((string) ($_POST['motor'] ?? ''));
    $form['display_name'] = trim((string) ($_POST['display_name'] ?? ''));
    $form['saddle'] = trim((string) ($_POST['saddle'] ?? ''));
    $form['cassette'] = trim((string) ($_POST['cassette'] ?? ''));
    $form['frame_spec'] = trim((string) ($_POST['frame_spec'] ?? ''));
    $form['fork_spec'] = trim((string) ($_POST['fork_spec'] ?? ''));
    $form['chain_spec'] = trim((string) ($_POST['chain_spec'] ?? ''));
    $form['note'] = trim((string) ($_POST['note'] ?? ''));
    $form['in_stock'] = isset($_POST['in_stock']) ? 1 : 0;

    return isset($_POST['remove_image']);
}

function admin_validate_bike_form(array $form, array &$errors): void
{
    if ($form['name'] === '') {
        $errors[] = 'NĂˇzev je povinnĂ˝.';
    }
    if ($form['category'] === '') {
        $errors[] = 'Kategorie je povinnĂˇ.';
    }
    if (!in_array($form['bike_type'], admin_bike_type_options(), true)) {
        $errors[] = 'Druh kola je neplatnĂ˝.';
    }
    if ($form['description'] === '') {
        $errors[] = 'Popis je povinnĂ˝.';
    }
    if ($form['price_czk'] === '' || !is_numeric($form['price_czk']) || (float) $form['price_czk'] < 0) {
        $errors[] = 'Cena musĂ­ bĂ˝t ÄŤĂ­slo 0 nebo vyĹˇĹˇĂ­.';
    }
    if ($form['old_price_czk'] !== '' && (!is_numeric($form['old_price_czk']) || (float) $form['old_price_czk'] < 0)) {
        $errors[] = 'PĹŻvodnĂ­ cena musĂ­ bĂ˝t ÄŤĂ­slo 0 nebo vyĹˇĹˇĂ­.';
    }
    if ($form['weight_kg'] !== '' && (!is_numeric($form['weight_kg']) || (float) $form['weight_kg'] < 0)) {
        $errors[] = 'Hmotnost musĂ­ bĂ˝t ÄŤĂ­slo 0 nebo vyĹˇĹˇĂ­.';
    }
}

function admin_handle_image_upload(array &$form, array &$errors, bool $removeImage): void
{
    $newUploadedImage = '';
    $uploadedFile = $_FILES['image_file'] ?? null;
    if (is_array($uploadedFile) && (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadError = (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errors[] = 'NahrĂˇnĂ­ obrĂˇzku selhalo.';
        } else {
            $tmpPath = (string) ($uploadedFile['tmp_name'] ?? '');
            $fileSize = (int) ($uploadedFile['size'] ?? 0);
            if ($fileSize > 5 * 1024 * 1024) {
                $errors[] = 'ObrĂˇzek je pĹ™Ă­liĹˇ velkĂ˝. MaximĂˇlnÄ› 5 MB.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = (string) $finfo->file($tmpPath);
                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif',
                ];
                if (!isset($extensions[$mimeType])) {
                    $errors[] = 'PovolenĂ© formĂˇty jsou JPG, PNG, WEBP a GIF.';
                } else {
                    $uploadDir = dirname(__DIR__) . '/uploads/bikes';
                    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                        $errors[] = 'NepodaĹ™ilo se vytvoĹ™it sloĹľku pro obrĂˇzky.';
                    } else {
                        $fileName = 'bike_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mimeType];
                        $targetPath = $uploadDir . '/' . $fileName;
                        if (move_uploaded_file($tmpPath, $targetPath)) {
                            $newUploadedImage = 'uploads/bikes/' . $fileName;
                        } else {
                            $errors[] = 'Soubor se nepodaĹ™ilo uloĹľit.';
                        }
                    }
                }
            }
        }
    }

    if ($errors !== []) {
        return;
    }

    if ($newUploadedImage !== '') {
        $oldImagePath = $form['image_url'];
        $form['image_url'] = $newUploadedImage;
        if ($oldImagePath !== '' && is_local_bike_image($oldImagePath)) {
            $oldAbsolutePath = local_bike_image_absolute_path($oldImagePath);
            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }
    } elseif ($removeImage) {
        $oldImagePath = $form['image_url'];
        $form['image_url'] = '';
        if ($oldImagePath !== '' && is_local_bike_image($oldImagePath)) {
            $oldAbsolutePath = local_bike_image_absolute_path($oldImagePath);
            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }
    }
}

function admin_handle_create_or_update(mysqli $mysqli, array &$form, array &$errors): void
{
    $action = (string) ($_POST['action'] ?? '');
    $removeImage = admin_populate_form_from_post($form);
    admin_validate_bike_form($form, $errors);
    admin_handle_image_upload($form, $errors, $removeImage);

    if ($errors !== []) {
        return;
    }

    $price = (float) $form['price_czk'];
    $oldPrice = $form['old_price_czk'];
    $isNew = (int) $form['is_new'];
    $weightKg = $form['weight_kg'];

    if ($action === 'create') {
        $stmt = $mysqli->prepare('INSERT INTO bikes (name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock) VALUES (?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt) {
            $createTypes = 'ssssdsi' . str_repeat('s', 26) . 'i';
            $stmt->bind_param($createTypes, $form['name'], $form['category'], $form['bike_type'], $form['manufacturer'], $price, $oldPrice, $isNew, $form['description'], $form['image_url'], $form['color'], $form['frame_size'], $form['wheel_size'], $weightKg, $form['tires'], $form['lighting'], $form['brakes'], $form['bottom_bracket'], $form['front_hub'], $form['rear_hub'], $form['front_rotor'], $form['rear_rotor'], $form['front_derailleur'], $form['rear_derailleur'], $form['wheels'], $form['battery'], $form['motor'], $form['display_name'], $form['saddle'], $form['cassette'], $form['frame_spec'], $form['fork_spec'], $form['chain_spec'], $form['note'], $form['in_stock']);
            try {
                $saved = $stmt->execute();
            } catch (Throwable $exception) {
                $stmt->close();
                $errors[] = 'PĹ™idĂˇnĂ­ kola selhalo pĹ™i uklĂˇdĂˇnĂ­ do databĂˇze.';
                return;
            }

            if ($saved) {
                $newBikeId = (int) $mysqli->insert_id;
                $stmt->close();
                write_audit_log($mysqli, 'create', 'bike', $newBikeId, [
                    'name' => $form['name'],
                    'category' => $form['category'],
                ]);
                admin_redirect_with_status('created', 'admin.php?edit=' . $newBikeId);
            }
            $stmt->close();
        }
        $errors[] = 'PĹ™idĂˇnĂ­ se nepodaĹ™ilo provĂ©st.';
        return;
    }

    if ($action === 'update') {
        $id = (int) $form['id'];
        if ($id <= 0) {
            $errors[] = 'NeplatnĂ© ID pro Ăşpravu.';
            return;
        }

        $stmt = $mysqli->prepare('UPDATE bikes SET name = ?, category = ?, bike_type = ?, manufacturer = ?, price_czk = ?, old_price_czk = NULLIF(?, \'\'), is_new = ?, description = ?, image_url = ?, color = ?, frame_size = ?, wheel_size = ?, weight_kg = NULLIF(?, \'\'), tires = ?, lighting = ?, brakes = ?, bottom_bracket = ?, front_hub = ?, rear_hub = ?, front_rotor = ?, rear_rotor = ?, front_derailleur = ?, rear_derailleur = ?, wheels = ?, battery = ?, motor = ?, display_name = ?, saddle = ?, cassette = ?, frame_spec = ?, fork_spec = ?, chain_spec = ?, note = ?, in_stock = ? WHERE id = ?');
        if ($stmt) {
            $updateTypes = 'ssssdsi' . str_repeat('s', 26) . 'ii';
            $stmt->bind_param($updateTypes, $form['name'], $form['category'], $form['bike_type'], $form['manufacturer'], $price, $oldPrice, $isNew, $form['description'], $form['image_url'], $form['color'], $form['frame_size'], $form['wheel_size'], $weightKg, $form['tires'], $form['lighting'], $form['brakes'], $form['bottom_bracket'], $form['front_hub'], $form['rear_hub'], $form['front_rotor'], $form['rear_rotor'], $form['front_derailleur'], $form['rear_derailleur'], $form['wheels'], $form['battery'], $form['motor'], $form['display_name'], $form['saddle'], $form['cassette'], $form['frame_spec'], $form['fork_spec'], $form['chain_spec'], $form['note'], $form['in_stock'], $id);
            try {
                $saved = $stmt->execute();
            } catch (Throwable $exception) {
                $stmt->close();
                $errors[] = 'UloĹľenĂ­ zmÄ›n kola selhalo pĹ™i uklĂˇdĂˇnĂ­ do databĂˇze.';
                return;
            }

            if ($saved) {
                $stmt->close();
                write_audit_log($mysqli, 'update', 'bike', $id, [
                    'name' => $form['name'],
                    'category' => $form['category'],
                ]);
                admin_redirect_with_status('updated', 'admin.php?edit=' . $id);
            }
            $stmt->close();
        }
        $errors[] = 'Ăšprava se nepodaĹ™ila provĂ©st.';
    }
}

function admin_import_csv_headers_match(array $headers): bool
{
    return admin_import_csv_normalized_headers($headers) !== null;
}

function admin_import_csv_strip_bom(string $value): string
{
    if (str_starts_with($value, "\xEF\xBB\xBF")) {
        return substr($value, 3);
    }

    return $value;
}

function admin_import_csv_normalize_header_name(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $value = strtr($value, [
        'Ăˇ' => 'a',
        'Ă¤' => 'a',
        'ÄŤ' => 'c',
        'ÄŹ' => 'd',
        'Ă©' => 'e',
        'Ä›' => 'e',
        'Ă«' => 'e',
        'Ă­' => 'i',
        'Äľ' => 'l',
        'Äş' => 'l',
        'Ĺ' => 'n',
        'Ăł' => 'o',
        'Ă¶' => 'o',
        'Ĺ™' => 'r',
        'Ĺ•' => 'r',
        'Ĺˇ' => 's',
        'ĹĄ' => 't',
        'Ăş' => 'u',
        'ĹŻ' => 'u',
        'ĂĽ' => 'u',
        'Ă˝' => 'y',
        'Ĺľ' => 'z',
    ]);

    return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
}

function admin_import_csv_header_aliases(): array
{
    return [
        'id' => 'ID',
        'nazev' => 'NĂˇzev',
        'kategorie' => 'Kategorie',
        'druhkola' => 'Druh kola',
        'typkola' => 'Druh kola',
        'biketype' => 'Druh kola',
        'vyrobce' => 'VĂ˝robce',
        'novacena' => 'Nov? cena',
        'puvodnicena' => 'PĹŻvodnĂ­ cena',
        'novinka' => 'Novinka',
        'popis' => 'Popis',
        'barva' => 'Barva',
        'velikostramu' => 'Velikost rĂˇmu',
        'velikostkol' => 'Velikost kol',
        'hmotnost' => 'Hmotnost',
        'pneu' => 'Pneu',
        'osvetleni' => 'OsvÄ›tlenĂ­',
        'brzdy' => 'Brzdy',
        'stred' => 'StĹ™ed',
        'razenivpredu' => 'ĹazenĂ­ vpĹ™edu',
        'razenivzadu' => 'ĹazenĂ­ vzadu',
        'kola' => 'Kola',
        'baterie' => 'Baterie',
        'motor' => 'Motor',
        'display' => 'Display',
        'poznamka' => 'PoznĂˇmka',
        'skladem' => 'Skladem',
        'obrazek' => 'ObrĂˇzek',
        'image' => 'ObrĂˇzek',
        'imageurl' => 'ObrĂˇzek',
        'stredkolapredni' => 'StĹ™ed kola pĹ™ednĂ­',
        'stredkolazadni' => 'StĹ™ed kola zadnĂ­',
        'diskrotorpredni' => 'Disk rotor pĹ™ednĂ­',
        'diskrotorzadni' => 'Disk rotor zadnĂ­',
        'sedlo' => 'Sedlo',
        'kazeta' => 'Kazeta',
        'ram' => 'RĂˇm',
        'vidlice' => 'Vidlice',
        'retez' => 'ĹetÄ›z',
    ];
}

function admin_normalize_bike_type_csv_value(string $value): string
{
    $trimmed = trim($value);
    if ($trimmed === '') {
        return 'uni';
    }

    $normalized = admin_import_csv_normalize_header_name($trimmed);
    $map = [
        'panske' => 'pĂˇnskĂ©',
        'damske' => 'dĂˇmskĂ©',
        'detske' => 'dÄ›tskĂ©',
        'junior' => 'dÄ›tskĂ©',
        'uni' => 'uni',
        'unisex' => 'uni',
    ];

    return $map[$normalized] ?? $trimmed;
}

function admin_import_csv_canonical_headers(array $headers): ?array
{
    $expected = admin_bike_csv_headers();
    if ($headers === $expected) {
        return $expected;
    }

    $aliases = admin_import_csv_header_aliases();
    $normalizedHeaders = [];
    foreach ($headers as $header) {
        $normalized = admin_import_csv_normalize_header_name((string) $header);
        if ($normalized === '' || !isset($aliases[$normalized])) {
            $normalizedHeaders = [];
            break;
        }

        $normalizedHeaders[] = $aliases[$normalized];
    }

    if ($normalizedHeaders !== []) {
        if (count(array_unique($normalizedHeaders)) !== count($normalizedHeaders)) {
            return null;
        }

        if ($normalizedHeaders === $expected) {
            return $expected;
        }

        $expectedWithoutBikeType = array_values(array_filter(
            $expected,
            static fn (string $header): bool => $header !== 'Druh kola'
        ));
        if ($normalizedHeaders === $expectedWithoutBikeType) {
            return $expectedWithoutBikeType;
        }
    }

    $legacy = ['ID', 'NĂ„â€šĂ‹â€ˇzev', 'Kategorie', 'Druh kola', 'VĂ„â€šĂ‹ĹĄrobce', 'NovĂ„â€šĂ‹â€ˇ cena', 'PĂ„Ä…ÄąÂ»vodnĂ„â€šĂ‚Â­ cena', 'Novinka', 'Popis', 'Barva', 'Velikost rĂ„â€šĂ‹â€ˇmu', 'Velikost kol', 'Hmotnost', 'Brzdy', 'StĂ„Ä…Ă˘â€žËed', 'Ă„Ä…Ă‚ÂazenĂ„â€šĂ‚Â­ vpĂ„Ä…Ă˘â€žËedu', 'Ă„Ä…Ă‚ÂazenĂ„â€šĂ‚Â­ vzadu', 'Kola', 'Baterie', 'Motor', 'Display', 'PoznĂ„â€šĂ‹â€ˇmka', 'Skladem', 'ObrĂ„â€šĂ‹â€ˇzek'];
    if ($headers === $legacy) {
        return $expected;
    }

    $legacyWithoutBikeType = ['ID', 'NĂ„â€šĂ‹â€ˇzev', 'Kategorie', 'VĂ„â€šĂ‹ĹĄrobce', 'NovĂ„â€šĂ‹â€ˇ cena', 'PĂ„Ä…ÄąÂ»vodnĂ„â€šĂ‚Â­ cena', 'Novinka', 'Popis', 'Barva', 'Velikost rĂ„â€šĂ‹â€ˇmu', 'Velikost kol', 'Hmotnost', 'Brzdy', 'StĂ„Ä…Ă˘â€žËed', 'Ă„Ä…Ă‚ÂazenĂ„â€šĂ‚Â­ vpĂ„Ä…Ă˘â€žËedu', 'Ă„Ä…Ă‚ÂazenĂ„â€šĂ‚Â­ vzadu', 'Kola', 'Baterie', 'Motor', 'Display', 'PoznĂ„â€šĂ‹â€ˇmka', 'Skladem', 'ObrĂ„â€šĂ‹â€ˇzek'];
    if ($headers === $legacyWithoutBikeType) {
        return [
            'ID',
            'NĂˇzev',
            'Kategorie',
            'VĂ˝robce',
            'NovĂˇ cena',
            'PĹŻvodnĂ­ cena',
            'Novinka',
            'Popis',
            'Barva',
            'Velikost rĂˇmu',
            'Velikost kol',
            'Hmotnost',
            'Brzdy',
            'StĹ™ed',
            'ĹazenĂ­ vpĹ™edu',
            'ĹazenĂ­ vzadu',
            'Kola',
            'Baterie',
            'Motor',
            'Display',
            'PoznĂˇmka',
            'Skladem',
            'ObrĂˇzek',
        ];
    }

    return null;
}

function admin_import_csv_expected_columns_normalized(): array
{
    return [
        'id',
        'nazev',
        'kategorie',
        'druhkola',
        'vyrobce',
        'novacena',
        'puvodnicena',
        'novinka',
        'popis',
        'barva',
        'velikostramu',
        'velikostkol',
        'hmotnost',
        'pneu',
        'osvetleni',
        'brzdy',
        'stred',
        'stredkolapredni',
        'stredkolazadni',
        'diskrotorpredni',
        'diskrotorzadni',
        'razenivpredu',
        'razenivzadu',
        'kola',
        'baterie',
        'motor',
        'display',
        'sedlo',
        'kazeta',
        'ram',
        'vidlice',
        'retez',
        'poznamka',
        'skladem',
        'obrazek',
    ];
}

function admin_import_csv_normalized_headers(array $headers): ?array
{
    $aliases = [
        'typkola' => 'druhkola',
        'biketype' => 'druhkola',
        'image' => 'obrazek',
        'imageurl' => 'obrazek',
    ];

    $normalizedHeaders = [];
    foreach ($headers as $header) {
        $normalized = admin_import_csv_normalize_header_name((string) $header);
        if ($normalized === '') {
            return null;
        }

        $normalizedHeaders[] = $aliases[$normalized] ?? $normalized;
    }

    if (count(array_unique($normalizedHeaders)) !== count($normalizedHeaders)) {
        return null;
    }

    $expected = admin_import_csv_expected_columns_normalized();
    if ($normalizedHeaders === $expected) {
        return $expected;
    }

    $expectedWithoutBikeType = array_values(array_filter(
        $expected,
        static fn (string $header): bool => $header !== 'druhkola'
    ));

    return $normalizedHeaders === $expectedWithoutBikeType ? $expectedWithoutBikeType : null;
}

function admin_import_csv_data_to_form(array $data): array
{
    $form = admin_default_bike_form();
    $form['name'] = (string) ($data['nazev'] ?? '');
    $form['category'] = (string) ($data['kategorie'] ?? '');
    $form['bike_type'] = admin_normalize_bike_type_csv_value((string) ($data['druhkola'] ?? 'uni'));
    $form['manufacturer'] = (string) ($data['vyrobce'] ?? '');
    $form['price_czk'] = (string) ($data['novacena'] ?? '');
    $form['old_price_czk'] = (string) ($data['puvodnicena'] ?? '');
    $form['is_new'] = admin_csv_truthy_to_int((string) ($data['novinka'] ?? ''));
    $form['description'] = (string) ($data['popis'] ?? '');
    $form['image_url'] = (string) ($data['obrazek'] ?? '');
    $form['color'] = (string) ($data['barva'] ?? '');
    $form['frame_size'] = normalize_multi_option_value((string) ($data['velikostramu'] ?? ''));
    $form['wheel_size'] = normalize_multi_option_value((string) ($data['velikostkol'] ?? ''));
    $form['weight_kg'] = (string) ($data['hmotnost'] ?? '');
    $form['tires'] = (string) ($data['pneu'] ?? '');
    $form['lighting'] = (string) ($data['osvetleni'] ?? '');
    $form['brakes'] = (string) ($data['brzdy'] ?? '');
    $form['bottom_bracket'] = (string) ($data['stred'] ?? '');
    $form['front_hub'] = (string) ($data['stredkolapredni'] ?? '');
    $form['rear_hub'] = (string) ($data['stredkolazadni'] ?? '');
    $form['front_rotor'] = (string) ($data['diskrotorpredni'] ?? '');
    $form['rear_rotor'] = (string) ($data['diskrotorzadni'] ?? '');
    $form['front_derailleur'] = (string) ($data['razenivpredu'] ?? '');
    $form['rear_derailleur'] = (string) ($data['razenivzadu'] ?? '');
    $form['wheels'] = (string) ($data['kola'] ?? '');
    $form['battery'] = (string) ($data['baterie'] ?? '');
    $form['motor'] = (string) ($data['motor'] ?? '');
    $form['display_name'] = (string) ($data['display'] ?? '');
    $form['saddle'] = (string) ($data['sedlo'] ?? '');
    $form['cassette'] = (string) ($data['kazeta'] ?? '');
    $form['frame_spec'] = (string) ($data['ram'] ?? '');
    $form['fork_spec'] = (string) ($data['vidlice'] ?? '');
    $form['chain_spec'] = (string) ($data['retez'] ?? '');
    $form['note'] = (string) ($data['poznamka'] ?? '');
    $form['in_stock'] = admin_csv_truthy_to_int((string) ($data['skladem'] ?? ''));

    return $form;
}

function admin_import_csv_row_is_empty(array $row): bool
{
    foreach ($row as $value) {
        if (trim((string) $value) !== '') {
            return false;
        }
    }

    return true;
}

function admin_import_find_existing_bike_id(mysqli $mysqli, int $id): bool
{
    $stmt = $mysqli->prepare('SELECT id FROM bikes WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result !== false && $result->fetch_assoc() !== null;
    $stmt->close();

    return $exists;
}

function admin_import_upsert_bike(mysqli $mysqli, array $form, int $id): ?array
{
    $price = (float) $form['price_czk'];
    $oldPrice = $form['old_price_czk'];
    $isNew = (int) $form['is_new'];
    $weightKg = $form['weight_kg'];
    $exists = $id > 0 && admin_import_find_existing_bike_id($mysqli, $id);

    if ($exists) {
        $stmt = $mysqli->prepare('UPDATE bikes SET name = ?, category = ?, bike_type = ?, manufacturer = ?, price_czk = ?, old_price_czk = NULLIF(?, \'\'), is_new = ?, description = ?, image_url = ?, color = ?, frame_size = ?, wheel_size = ?, weight_kg = NULLIF(?, \'\'), tires = ?, lighting = ?, brakes = ?, bottom_bracket = ?, front_hub = ?, rear_hub = ?, front_rotor = ?, rear_rotor = ?, front_derailleur = ?, rear_derailleur = ?, wheels = ?, battery = ?, motor = ?, display_name = ?, saddle = ?, cassette = ?, frame_spec = ?, fork_spec = ?, chain_spec = ?, note = ?, in_stock = ? WHERE id = ?');
        if (!$stmt) {
            return null;
        }

        $updateTypes = 'ssssdsi' . str_repeat('s', 26) . 'ii';
        $stmt->bind_param($updateTypes, $form['name'], $form['category'], $form['bike_type'], $form['manufacturer'], $price, $oldPrice, $isNew, $form['description'], $form['image_url'], $form['color'], $form['frame_size'], $form['wheel_size'], $weightKg, $form['tires'], $form['lighting'], $form['brakes'], $form['bottom_bracket'], $form['front_hub'], $form['rear_hub'], $form['front_rotor'], $form['rear_rotor'], $form['front_derailleur'], $form['rear_derailleur'], $form['wheels'], $form['battery'], $form['motor'], $form['display_name'], $form['saddle'], $form['cassette'], $form['frame_spec'], $form['fork_spec'], $form['chain_spec'], $form['note'], $form['in_stock'], $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success ? ['action' => 'update', 'id' => $id] : null;
    }

    if ($id > 0) {
        $stmt = $mysqli->prepare('INSERT INTO bikes (id, name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock) VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            return null;
        }

        $insertWithIdTypes = 'issssdsi' . str_repeat('s', 26) . 'i';
        $stmt->bind_param($insertWithIdTypes, $id, $form['name'], $form['category'], $form['bike_type'], $form['manufacturer'], $price, $oldPrice, $isNew, $form['description'], $form['image_url'], $form['color'], $form['frame_size'], $form['wheel_size'], $weightKg, $form['tires'], $form['lighting'], $form['brakes'], $form['bottom_bracket'], $form['front_hub'], $form['rear_hub'], $form['front_rotor'], $form['rear_rotor'], $form['front_derailleur'], $form['rear_derailleur'], $form['wheels'], $form['battery'], $form['motor'], $form['display_name'], $form['saddle'], $form['cassette'], $form['frame_spec'], $form['fork_spec'], $form['chain_spec'], $form['note'], $form['in_stock']);
        $success = $stmt->execute();
        $stmt->close();

        return $success ? ['action' => 'create', 'id' => $id] : null;
    }

    $stmt = $mysqli->prepare('INSERT INTO bikes (name, category, bike_type, manufacturer, price_czk, old_price_czk, is_new, description, image_url, color, frame_size, wheel_size, weight_kg, tires, lighting, brakes, bottom_bracket, front_hub, rear_hub, front_rotor, rear_rotor, front_derailleur, rear_derailleur, wheels, battery, motor, display_name, saddle, cassette, frame_spec, fork_spec, chain_spec, note, in_stock) VALUES (?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        return null;
    }

    $insertTypes = 'ssssdsi' . str_repeat('s', 26) . 'i';
    $stmt->bind_param($insertTypes, $form['name'], $form['category'], $form['bike_type'], $form['manufacturer'], $price, $oldPrice, $isNew, $form['description'], $form['image_url'], $form['color'], $form['frame_size'], $form['wheel_size'], $weightKg, $form['tires'], $form['lighting'], $form['brakes'], $form['bottom_bracket'], $form['front_hub'], $form['rear_hub'], $form['front_rotor'], $form['rear_rotor'], $form['front_derailleur'], $form['rear_derailleur'], $form['wheels'], $form['battery'], $form['motor'], $form['display_name'], $form['saddle'], $form['cassette'], $form['frame_spec'], $form['fork_spec'], $form['chain_spec'], $form['note'], $form['in_stock']);
    $success = $stmt->execute();
    $newId = (int) $mysqli->insert_id;
    $stmt->close();

    return $success ? ['action' => 'create', 'id' => $newId] : null;
}

function admin_handle_import_bikes_csv(mysqli $mysqli, array &$errors): void
{
    $uploadedFile = $_FILES['bikes_csv'] ?? null;
    if (!is_array($uploadedFile)) {
        $errors[] = 'CSV soubor nebyl nahrĂˇn.';
        return;
    }

    $uploadError = (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Vyber CSV soubor pro import.';
        return;
    }
    if ($uploadError !== UPLOAD_ERR_OK) {
        $errors[] = 'NahrĂˇnĂ­ CSV souboru selhalo.';
        return;
    }

    $tmpPath = (string) ($uploadedFile['tmp_name'] ?? '');
    $handle = fopen($tmpPath, 'rb');
    if ($handle === false) {
        $errors[] = 'CSV soubor se nepodaĹ™ilo otevĹ™Ă­t.';
        return;
    }

    $headers = fgetcsv($handle, 0, ';');
    if (!is_array($headers)) {
        fclose($handle);
        $errors[] = 'CSV soubor je prĂˇzdnĂ˝.';
        return;
    }

    $headers = array_map(static fn ($value): string => trim(admin_import_csv_strip_bom((string) $value)), $headers);
    $canonicalHeaders = admin_import_csv_normalized_headers($headers);
    if ($canonicalHeaders === null) {
        fclose($handle);
        $errors[] = 'CSV hlaviÄŤka neodpovĂ­dĂˇ exportovanĂ©mu formĂˇtu seznamu kol.';
        return;
    }

    $rowNumber = 1;
    $importedRows = 0;
    $mysqli->begin_transaction();

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        $rowNumber++;
        $row = array_pad($row, count($canonicalHeaders), '');
        if (admin_import_csv_row_is_empty($row)) {
            continue;
        }

        $data = array_combine($canonicalHeaders, array_map(static fn ($value): string => trim((string) $value), $row));
        if ($data === false) {
            $errors[] = 'ĹĂˇdek ' . $rowNumber . ': nepodaĹ™ilo se pĹ™eÄŤĂ­st hodnoty CSV.';
            break;
        }

        $form = admin_default_bike_form();
        $form['name'] = (string) ($data['NĂˇzev'] ?? '');
        $form['category'] = (string) ($data['Kategorie'] ?? '');
        $form['bike_type'] = admin_normalize_bike_type_csv_value((string) ($data['Druh kola'] ?? 'uni'));
        $form['manufacturer'] = (string) ($data['VĂ˝robce'] ?? '');
        $form['price_czk'] = (string) ($data['NovĂˇ cena'] ?? '');
        $form['old_price_czk'] = (string) ($data['PĹŻvodnĂ­ cena'] ?? '');
        $form['is_new'] = admin_csv_truthy_to_int((string) ($data['Novinka'] ?? ''));
        $form['description'] = (string) ($data['Popis'] ?? '');
        $form['image_url'] = (string) ($data['ObrĂˇzek'] ?? '');
        $form['color'] = (string) ($data['Barva'] ?? '');
        $form['frame_size'] = normalize_multi_option_value((string) ($data['Velikost rĂˇmu'] ?? ''));
        $form['wheel_size'] = normalize_multi_option_value((string) ($data['Velikost kol'] ?? ''));
        $form['weight_kg'] = (string) ($data['Hmotnost'] ?? '');
        $form['tires'] = (string) ($data['Pneu'] ?? '');
        $form['lighting'] = (string) ($data['OsvĂ„â€ştlenÄ‚Â­'] ?? '');
        $form['brakes'] = (string) ($data['Brzdy'] ?? '');
        $form['front_hub'] = (string) ($data['StÄąâ„˘ed kola pÄąâ„˘ednÄ‚Â­'] ?? '');
        $form['rear_hub'] = (string) ($data['StÄąâ„˘ed kola zadnÄ‚Â­'] ?? '');
        $form['front_rotor'] = (string) ($data['Disk rotor pÄąâ„˘ednÄ‚Â­'] ?? '');
        $form['rear_rotor'] = (string) ($data['Disk rotor zadnÄ‚Â­'] ?? '');
        $form['bottom_bracket'] = (string) ($data['StĹ™ed'] ?? '');
        $form['front_derailleur'] = (string) ($data['ĹazenĂ­ vpĹ™edu'] ?? '');
        $form['rear_derailleur'] = (string) ($data['ĹazenĂ­ vzadu'] ?? '');
        $form['wheels'] = (string) ($data['Kola'] ?? '');
        $form['battery'] = (string) ($data['Baterie'] ?? '');
        $form['motor'] = (string) ($data['Motor'] ?? '');
        $form['display_name'] = (string) ($data['Display'] ?? '');
        $form['saddle'] = (string) ($data['Sedlo'] ?? '');
        $form['cassette'] = (string) ($data['Kazeta'] ?? '');
        $form['frame_spec'] = (string) ($data['RÄ‚Ë‡m'] ?? '');
        $form['fork_spec'] = (string) ($data['Vidlice'] ?? '');
        $form['chain_spec'] = (string) ($data['ÄąÂetĂ„â€şz'] ?? '');
        $form['note'] = (string) ($data['PoznĂˇmka'] ?? '');
        $form['in_stock'] = admin_csv_truthy_to_int((string) ($data['Skladem'] ?? ''));

        $form = admin_import_csv_data_to_form($data);

        $rowErrors = [];
        admin_validate_bike_form($form, $rowErrors);
        if ($rowErrors !== []) {
            $errors[] = 'ĹĂˇdek ' . $rowNumber . ': ' . $rowErrors[0];
            break;
        }

        $idValue = trim((string) ($data['id'] ?? ''));
        $id = ctype_digit($idValue) ? (int) $idValue : 0;
        $result = admin_import_upsert_bike($mysqli, $form, $id);
        if ($result === null) {
            $errors[] = 'ĹĂˇdek ' . $rowNumber . ': import se nepodaĹ™ilo uloĹľit do databĂˇze.';
            break;
        }

        write_audit_log($mysqli, $result['action'], 'bike', (int) $result['id'], [
            'source' => 'csv_import',
            'name' => $form['name'],
            'category' => $form['category'],
        ]);
        $importedRows++;
    }

    fclose($handle);

    if ($errors !== []) {
        $mysqli->rollback();
        return;
    }

    if ($importedRows === 0) {
        $mysqli->rollback();
        $errors[] = 'CSV neobsahuje ĹľĂˇdnĂ˝ Ĺ™Ăˇdek s koly pro import.';
        return;
    }

    $mysqli->commit();
    admin_redirect_with_status('imported');
}

function handle_admin_post(mysqli $mysqli, array &$form, array &$errors, string &$noticeBulkText, int &$noticeBulkActive): void
{
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        admin_handle_delete($mysqli, $errors);
    }
    if ($action === 'duplicate') {
        admin_handle_duplicate($mysqli, $errors);
    }
    if ($action === 'add_notices') {
        admin_handle_add_notices($mysqli, $errors, $noticeBulkText, $noticeBulkActive);
    }
    if ($action === 'toggle_notice') {
        admin_handle_toggle_notice($mysqli, $errors);
    }
    if ($action === 'delete_notice') {
        admin_handle_delete_notice($mysqli, $errors);
    }
    if ($action === 'create_service_bike_serial') {
        admin_handle_create_service_bike_serial($mysqli, $errors);
    }
    if ($action === 'delete_service_bike_serial') {
        admin_handle_delete_service_bike_serial($mysqli, $errors);
    }
    if ($action === 'save_service_sheet') {
        admin_handle_save_service_sheet($mysqli, $errors);
    }
    if ($action === 'send_service_sheet_email') {
        admin_handle_send_service_sheet_email($mysqli, $errors);
    }
    if ($action === 'update_service_access_password') {
        admin_handle_update_service_access_password($mysqli, $errors);
    }
    if ($action === 'update_service_reservation_status') {
        admin_handle_update_service_reservation_status($mysqli, $errors);
    }
    if ($action === 'delete_service_reservation') {
        admin_handle_delete_service_reservation($mysqli, $errors);
    }
    if ($action === 'confirm_service_reservation') {
        admin_handle_service_reservation_email_action($mysqli, $errors, 'potvrdit');
    }
    if ($action === 'reject_service_reservation') {
        admin_handle_service_reservation_email_action($mysqli, $errors, 'odmitnout');
    }
    if ($action === 'import_bikes_csv') {
        admin_handle_import_bikes_csv($mysqli, $errors);
    }
    if ($action === 'create' || $action === 'update') {
        admin_handle_create_or_update($mysqli, $form, $errors);
    }
}
