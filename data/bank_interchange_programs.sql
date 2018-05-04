DELIMITER //


--
-- Procedures
--


CREATE PROCEDURE assignments_validate(
  IN new_bank int,
  IN new_document_kind int,
  IN new_wallet int,
  IN new_cnab enum('240', '400')
) READS SQL DATA
BEGIN
  DECLARE kind_bank, wallet_bank int;
  DECLARE kind_cnab, wallet_cnab enum('240', '400');

  SELECT bank, cnab
  INTO   kind_bank, kind_cnab
  FROM   document_kinds WHERE id=new_document_kind;

  SELECT bank, cnab
  INTO   wallet_bank, wallet_cnab
  FROM   wallets WHERE id=new_wallet;

  IF new_bank <> kind_bank OR new_bank <> wallet_bank
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: bank, document_kind.bank and wallet.bank are different';
  END IF;

  IF new_cnab <> kind_cnab OR new_cnab <> wallet_cnab
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: cnab, document_kind.cnab and wallet.cnab are different';
  END IF;
END//


CREATE PROCEDURE titles_validate(
  IN new_shipping_file int,
  IN new_movement int,
  IN new_assignment int,
  IN new_client int,
  IN new_guarantor int,
  IN new_kind int,
  IN new_emission date,
  IN new_due date,
  IN fine_type int,
  INOUT fine_date date,
  INOUT fine_value decimal,
  IN interest_type int,
  INOUT interest_date date,
  INOUT interest_value decimal,
  IN discount1_type int,
  INOUT discount1_date date,
  INOUT discount1_value decimal,
  IN discount2_type int,
  INOUT discount2_date date,
  INOUT discount2_value decimal,
  IN discount3_type int,
  INOUT discount3_date date,
  INOUT discount3_value decimal
) READS SQL DATA
BEGIN
  DECLARE movement_bank, assignment_assignor, assignment_bank int;
  DECLARE movement_cnab, assignment_cnab enum('240', '400');

  SELECT assignor, bank, cnab
  INTO   assignment_assignor, assignment_bank, assignment_cnab
  FROM   assignments WHERE id=new_assignment;

  IF new_shipping_file IS NOT NULL
    AND new_assignment <> (SELECT assignment FROM shipping_files WHERE id=new_shipping_file)
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: assignment and shipping_file.assignment are different';
  END IF;

  IF new_movement IS NOT NULL
  THEN
    SELECT bank, cnab
    INTO   movement_bank, movement_cnab
    FROM   shipping_file_movements WHERE id=new_movement;

    IF assignment_bank <> movement_bank
    THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot add or update row: assignment.bank and movement.bank are different';
    END IF;

    IF assignment_cnab <> movement_cnab
    THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot add or update row: assignment.cnab and movement.cnab are different';
    END IF;
  END IF;

  IF assignment_assignor <> (SELECT assignor FROM clients WHERE id=new_client)
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: assignment.assignor and client.assignor are different';
  END IF;

  IF new_guarantor IS NOT NULL
    AND assignment_assignor <> (SELECT assignor FROM clients WHERE id=new_guarantor)
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: assignment.assignor and guarantor.assignor are different';
  END IF;

  IF assignment_bank <> (SELECT bank FROM document_kinds WHERE id=new_kind)
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: assignment.bank and kind.bank are different';
  END IF;

  IF new_due < new_emission
  THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot add or update row: due date is earlier than emission';
  END IF;

  IF fine_type = 0
  THEN
    SET fine_date = NULL;
    SET fine_value = NULL;
  END IF;

  IF interest_type = 3
  THEN
    SET interest_date = NULL;
    SET interest_value = NULL;
  END IF;

  IF discount1_type = 0
  THEN
    SET discount1_date = NULL;
    SET discount1_value = NULL;
  END IF;

  IF discount2_type = 0
  THEN
    SET discount2_date = NULL;
    SET discount2_value = NULL;
  END IF;

  IF discount3_type = 0
  THEN
    SET discount3_date = NULL;
    SET discount3_value = NULL;
  END IF;
END//


--
-- Triggers
--


CREATE TRIGGER assignments_before_insert
BEFORE INSERT ON assignments FOR EACH ROW
CALL assignments_validate(NEW.bank, NEW.document_kind, NEW.wallet, NEW.cnab)//


CREATE TRIGGER assignments_before_update
BEFORE UPDATE ON assignments FOR EACH ROW
CALL assignments_validate(NEW.bank, NEW.document_kind, NEW.wallet, NEW.cnab)//


CREATE TRIGGER titles_before_insert
BEFORE INSERT ON titles FOR EACH ROW
BEGIN
  DECLARE prev_doc_number, prev_our_number int;

  SELECT IFNULL(MAX(doc_number), 0), IFNULL(MAX(our_number), 0)
  INTO   prev_doc_number, prev_our_number
  FROM   titles WHERE assignment=NEW.assignment;

  IF NEW.doc_number IS NULL
  THEN
    SET NEW.doc_number = prev_doc_number + 1;
  END IF;

  IF NEW.our_number IS NULL
  THEN
    SET NEW.our_number = prev_our_number + 1;
  END IF;

  IF NEW.emission IS NULL
  THEN
    SET NEW.emission = NOW();
  END IF;

  CALL titles_validate(
    NEW.shipping_file,
    NEW.movement,
    NEW.assignment,
    NEW.client,
    NEW.guarantor,
    NEW.kind,
    NEW.emission,
    NEW.due,
    NEW.fine_type,
    NEW.fine_date,
    NEW.fine_value,
    NEW.interest_type,
    NEW.interest_date,
    NEW.interest_value,
    NEW.discount1_type,
    NEW.discount1_date,
    NEW.discount1_value,
    NEW.discount2_type,
    NEW.discount2_date,
    NEW.discount2_value,
    NEW.discount3_type,
    NEW.discount3_date,
    NEW.discount3_value
  );
END//


CREATE TRIGGER titles_before_update
BEFORE UPDATE ON titles FOR EACH ROW
CALL titles_validate(
  NEW.shipping_file,
  NEW.movement,
  NEW.assignment,
  NEW.client,
  NEW.guarantor,
  NEW.kind,
  NEW.emission,
  NEW.due,
  NEW.fine_type,
  NEW.fine_date,
  NEW.fine_value,
  NEW.interest_type,
  NEW.interest_date,
  NEW.interest_value,
  NEW.discount1_type,
  NEW.discount1_date,
  NEW.discount1_value,
  NEW.discount2_type,
  NEW.discount2_date,
  NEW.discount2_value,
  NEW.discount3_type,
  NEW.discount3_date,
  NEW.discount3_value
)//


CREATE TRIGGER shipping_files_before_insert
BEFORE INSERT ON shipping_files FOR EACH ROW
BEGIN
  IF NEW.counter IS NULL
  THEN
    SET NEW.counter = (SELECT IFNULL(MAX(counter), 0) FROM shipping_files
      WHERE assignment=NEW.assignment) + 1;
  END IF;
END//


DELIMITER ;
