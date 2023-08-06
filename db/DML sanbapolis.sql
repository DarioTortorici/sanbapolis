START TRANSACTION;

INSERT INTO persone (email, nome, cognome, data_nascita, citta, indirizzo, telefono, digest_password, locazione_immagine_profilo, verificato, data_ora_registrazione) VALUES
/*tifosi*/
('mario.rossi@gmail.com', 'Mario', 'Rossi', '2000:01:01', 'Trento', 'via Roma 1', '0423 666 6666', '$2y$10$p.kA2CmhHwEGX118sOJdauL0i2sKkziXzwNv4BaWr.RN7EN1TxuL6', NULL, TRUE, NOW()), 

('giovanna.rossi@gmail.com', 'Giovanna', 'Rossi', '1998-03-15', 'Firenze', 'Via Dante 20', '055 1234 5678', '$2y$10$IE83LKlX1Zw8eL/ZEezFYeGd2O9uGGbheBRnY2fqAMuthv4gz8Pba', NULL, TRUE, NOW()),
('marco.romano@gmail.com', 'Marco', 'Romano', '1990-09-05', 'Bologna', 'Viale dei Mille 30', '051 9876 5432', '$2y$10$rPQRyNlYI/5SeUZF8.EfD.oTEYLKcvGNEq0IWvR4mnOfJz40NZTMi', NULL, TRUE, NOW()),
('silvia.conti@gmail.com', 'Silvia', 'Conti', '1993-06-22', 'Genova', 'Piazza De Ferrari 10', '010 5555 5555', '$2y$10$Oy4tw2.va.QreSNAZrGCce/9u4SjRmSmaOWVSVBxCt/doSLUJurCa', NULL, TRUE, NOW()),
('fabio.mancini@gmail.com', 'Fabio', 'Mancini', '1987-12-08', 'Palermo', 'Via Libertà 5', '091 1234 5678', '$2y$10$ZIlQrF1lD2Ug5cAH0mpFc.2.ziSyjTC/vWhBsJWyKWLY0D9uZtDMu', NULL, TRUE, NOW()),
('elisa.ricci@gmail.com', 'Elisa', 'Ricci', '1996-07-04', 'Venezia', 'Calle Larga XXII Marzo 15', '041 9876 5432', '$2y$10$z9TLOrILIVOb9GADYdvsCOI0CzJb8IKxXFlXDCaOfFN3MfgeRc3si', NULL, TRUE, NOW()),



/*allenatori*/
('vincenzo.italiano@gmail.com', 'Vincenzo', 'Italiano', '1982:01:01', 'Firenze', 'via Firenze 1', '0423 666 1234', '$2y$10$FO62apRI7o13EO5pIkcHw.gVrO7AMbs271K2F1jT02Hfce75VW5JG', NULL, TRUE, NOW()),
('raffaele.palladino@gmail.com', 'Raffaele', 'Palladino', '1980:01:01', 'Monza', 'via Monza 1', '0423 666 1234','$2y$10$st/crDm4qBjRZ64SF4k/4OIXdK6dnS.WPINKPHn8ncB5PzUEFdUD2', NULL, TRUE, NOW()),
('giorgio.verdi@gmail.com', 'Giorgio', 'Verdi', '1982-06-15', 'Milano', 'via Garibaldi 6', '333 1111111', '$2y$10$wc7yIuNpygQmoOiDuuenzurPvgiR41mkOX3vnQDwdlC9sI/NwavJm', NULL, TRUE, NOW()),
('luisa.bianchi@gmail.com', 'Luisa', 'Bianchi', '1985-02-25', 'Roma', 'via Tuscolana 8', '345 2222222', '$2y$10$jDdXApfXI6yODX53LpwpSuBAxeWWVPnZzhfwO6TJh7uZg91UdhiSy', NULL, TRUE, NOW()),
/*giocatori*/
('antonio.verdi@gmail.com', 'Antonio', 'Verdi', '2000:01:01', 'Trento', 'via Garibaldi 1', '6666 666 6666', '$2y$10$0hFz0iQXg8C0qsnYkC6sm.YLRN9ar2StbGicDf8D67zgVkgmbb37i', NULL, TRUE, NOW()),
('manolo.pisani@gmail.com', 'Manolo', 'Pisani', '1997:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$JFbZflTCFbCvEc5GllVHQuUCSZ4pPWpd8KQ3F8yf9h1IjfWYFwypO', NULL, TRUE, NOW()),
('costanzo.greco@gmail.com', 'Costanzo', 'Greco', '2001:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$qwA5olbnr0FonU8ZZVl9j.bPcnX1DBjNn3dv6PsnYcA0Hl2PHHCMi', NULL, TRUE, NOW()),
('arcangelo.milanesi@gmail.com', 'Arcangelo', 'Milanesi', '1994:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$LOplK8hw6mriCiiXsbxPkejtkEHjzVhMIv2JupCn1YYhgZfkMqxXi', NULL, TRUE, NOW()),
('emmanuele.cremonesi@gmail.com', 'Emmanuele', 'Cremonesi', '2000:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$Sc00Qtgl9ktTJRhggPvwzu29fjan.lwzNeEK7PqWMPiex9V4Ug5Ma', NULL, TRUE, NOW()),
('lorenzo.agenore@gmail.com', 'Lorenzo', 'Agenore', '2000:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$uxR.eT9tK5StTr7jgXm6Z.4/V6CC5MxKnZo7L5NZ9IZkj3BO.xIZS', NULL, TRUE, NOW()),
('fulvio.dellucci@gmail.com', 'Fulvio', 'Dellucci', '2004:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$Lpg/pgMAmeScZFbY5Dxc4ep4ne9Ptdst5Vd9GPfmFw/WQjzf8wKxG', NULL, TRUE, NOW()),
('oliviero.palermo@gmail.com', 'Oliviero', 'Palermo', '1991:01:01', 'Trento', 'via Roma 1', '0423 666 1234','$2y$10$vXXpaSmW99l3G306Io.oteb1MY4uDgMoooc8D7nG55dP4HUdbOAP', NULL, TRUE, NOW()),
('giovanni.bianchi@gmail.com', 'Giovanni', 'Bianchi', '1992-05-15', 'Roma', 'via Appia 2', '345 6789123', '$2y$10$hDBSqQkghlDrB0mhn1HlqeCNLQGDvVJdxOjHZzxl/9Qq14ryiuRB2', NULL, TRUE, NOW()),
('laura.verdi@gmail.com', 'Laura', 'Verdi', '1993-08-22', 'Milano', 'via Garibaldi 3', '333 4567890', '$2y$10$5/nz0sup0rZP0vfVzB6r8evk2Avw5cXb.2zMpxtT0ZfusRmxKZzqi', NULL, TRUE, NOW()),
('giuseppe.rossini@gmail.com', 'Giuseppe', 'Rossini', '1994-02-10', 'Napoli', 'via Vesuvio 4', '333 9876543', '$2y$10$6PhhvMGIfZzR1whS6.uISOxqyRLsdO2srLRyIPQ6Ueyr6RM33sfqK', NULL, TRUE, NOW()),
('francesca.ferrari@gmail.com', 'Francesca', 'Ferrari', '1995-11-27', 'Torino', 'via Po 5', '345 1234567', '$2y$10$VBdTIvUUlobr3tjslhs7tutOjs9PAgqeo6OLB74elllMQhxdknyDG', NULL, TRUE, NOW()),
('marco.verdi@gmail.com', 'Marco', 'Verdi', '1990-07-12', 'Firenze', 'via dei Mille 6', '345 9876543', '$2y$10$EK4GwVv9hXRbkquMTcjI4uHyM0v3Ozxe89G/8Jxw10P/mORxpHb0y', NULL, TRUE, NOW()),
('simone.bianchi@gmail.com', 'Simone', 'Bianchi', '1991-03-26', 'Roma', 'via Tuscolana 7', '333 1234567', '$2y$10$nbkRv/I8caS9buAGXvrWX.qNJ7dK17zaXrV4bdpgTfcvbvyQeNXuG', NULL, TRUE, NOW()),
('luigi.rossi@gmail.com', 'Luigi', 'Rossi', '1992-09-09', 'Milano', 'via Milano 8', '0423 9876543', '$2y$10$18XNL9OQerGlwkVhIKGgeOZM17bvpGtH0TTN6kd947cFabP0OqC0u', NULL, TRUE, NOW()),
('andrea.ferrari@gmail.com', 'Andrea', 'Ferrari', '1993-12-03', 'Torino', 'via Garibaldi 9', '333 4567890', '$2y$10$zciBQwtmPPWbiaLVp9GpAe0jBdElxVXekow/Dn0dYnRJ2aisL3Cq6', NULL, TRUE, NOW()),
('paolo.verdi@gmail.com', 'Paolo', 'Verdi', '1994-05-18', 'Firenze', 'via dei Mille 10', '345 1234567', '$2y$10$CnGqk3u4JPkbpV71mmxmruKUZBiuV.ht93fx7x7HBoz/OQ/BXVzty', NULL, TRUE, NOW()),
('roberto.bianchi@gmail.com', 'Roberto', 'Bianchi', '1995-10-31', 'Roma', 'via Tuscolana 11', '333 9876543', '$2y$10$TSSGsqgpAMNm83WWI72KlOJw6CSvrrtH/zJBn40MHXzAeuNjKV7Wy', NULL, TRUE, NOW()),
('antonio.rossi@gmail.com', 'Antonio', 'Rossi', '1996-02-14', 'Milano', 'via Milano 12', '0423 4567890', '$2y$10$mnGSZzZLUASqOHXGtwqGKeqKASynH12htQ6qGT7O0NZoKYnMakaIS', NULL, TRUE, NOW()),
('davide.ferrari@gmail.com', 'Davide', 'Ferrari', '1997-07-30', 'Torino', 'via Garibaldi 13', '333 1234567', '$2y$10$IC2MSP/60WoF9w/qkBvMw.6BnW47o98N4RxfWQO84QMQVQBzhzpLW', NULL, TRUE, NOW()),
('riccardo.verdi@gmail.com', 'Riccardo', 'Verdi', '1998-04-05', 'Firenze', 'via dei Mille 14', '345 9876543', '$2y$10$4D2H2EDd4wTjHpgI8r8/3uBUDWtqkTyFEOCKfDzrKkl0eY/H.nbAa', NULL, TRUE, NOW()),
('enrico.bianchi@gmail.com', 'Enrico', 'Bianchi', '1999-09-19', 'Roma', 'via Tuscolana 15', '333 4567890', '$2y$10$mWZsK3C31TVmrC25xtoKYeJDwxTXLGZdnWWsrut19BfJF44RV96nu', NULL, TRUE, NOW()),
('marco.rossi@gmail.com', 'Marco', 'Rossi', '2000-03-04', 'Milano', 'via Milano 16', '0423 1234567', '$2y$10$EK4GwVv9hXRbkquMTcjI4uHyM0v3Ozxe89G/8Jxw10P/mORxpHb0y', NULL, TRUE, NOW()),
('gabriele.ferrari@gmail.com', 'Gabriele', 'Ferrari', '2001-08-20', 'Torino', 'via Garibaldi 17', '333 9876543', '$2y$10$dZj7Jl2Qnh0pg55T8aUM9.Ag.upNhWdd9L1nuxjaNOcCU7hViUOyO', NULL, TRUE, NOW()),
('michele.verdi@gmail.com', 'Michele', 'Verdi', '2002-01-05', 'Firenze', 'via dei Mille 18', '345 4567890', '$2y$10$sfQF.R91TxpYNZet5pPkq.zR8xLaYU5ni6YojRfPIJ3BPVS2VZ2ue', NULL, TRUE, NOW()),
('andrea.bianchi@gmail.com', 'Andrea', 'Bianchi', '2003-06-21', 'Roma', 'via Tuscolana 19', '333 1234567', '$2y$10$zciBQwtmPPWbiaLVp9GpAe0jBdElxVXekow/Dn0dYnRJ2aisL3Cq6', NULL, TRUE, NOW()),
('luca.rossi@gmail.com', 'Luca', 'Rossi', '2004-11-06', 'Milano', 'via Milano 20', '0423 9876543', '$2y$10$MDpf0aXSqSmbufkM7mz/ze7VacJnsJ4Tv76OyQl16pLlIyFaDMCVO', NULL, TRUE, NOW()),
/*resposabile*/
('paola.verdi@gmail.com', 'Paola', 'Verdi', '1966-08-12', 'Milano', 'via Garibaldi 3', '333 4567890', '$2y$10$yszcrtb0zhPmFy3k0l7aB.m4nJkDphwioszY9Ftn2asRl1FtwOfk2', NULL, TRUE, NOW()),
('carlo.rossini@gmail.com', 'Carlo', 'Rossini', '1967-11-02', 'Napoli', 'via Vesuvio 4', '333 9876543', '$2y$10$BOSNsxjT7r4xUu5/PmAjH.7ZNUCb4Xrg2MWEMZR/EmQc21FzRLfnm', NULL, TRUE, NOW()),
('elisa.ferrari@gmail.com', 'Elisa', 'Ferrari', '1968-05-18', 'Torino', 'via Po 5', '345 1234567', '$2y$10$LVSguxik1KqKe0IPIBj5/OVSJNmIjJV87smzckFQcKOPju5ULWnwO', NULL, TRUE, NOW()),
('luigi.bianchi@gmail.com', 'Luigi', 'Bianchi', '1969-12-09', 'Firenze', 'via dei Mille 6', '345 9876543', '$2y$10$JAaOUSkR3Yul7b3QXd5WnOSgkz945pyU6CuahhaklamhRNeMxPxPa', NULL, TRUE, NOW());

INSERT INTO tipi_allenatori (nome_tipo) VALUES ('Allenatore'), ('Viceallenatore');

INSERT INTO allenatori (email, tipo, privilegi_cam) VALUES 
('vincenzo.italiano@gmail.com', 'Allenatore', TRUE),
('raffaele.palladino@gmail.com', 'Allenatore', TRUE),
('giorgio.verdi@gmail.com', 'Allenatore', TRUE),
('luisa.bianchi@gmail.com', 'Allenatore', TRUE);

INSERT INTO tifosi (email) VALUES 
('mario.rossi@gmail.com'),
('giovanna.rossi@gmail.com'),
('marco.romano@gmail.com'),
('silvia.conti@gmail.com'),
('fabio.mancini@gmail.com'),
('elisa.ricci@gmail.com');

INSERT INTO giocatori (email) VALUES 
('antonio.verdi@gmail.com'),
('manolo.pisani@gmail.com'),
('costanzo.greco@gmail.com'),
('arcangelo.milanesi@gmail.com'),
('emmanuele.cremonesi@gmail.com'),
('lorenzo.agenore@gmail.com'),
('fulvio.dellucci@gmail.com'),
('oliviero.palermo@gmail.com'),
('giovanni.bianchi@gmail.com'),
('laura.verdi@gmail.com'),
('giuseppe.rossini@gmail.com'),
('francesca.ferrari@gmail.com'),
('marco.verdi@gmail.com'),
('simone.bianchi@gmail.com'),
('luigi.rossi@gmail.com'),
('andrea.ferrari@gmail.com'),
('paolo.verdi@gmail.com'),
('roberto.bianchi@gmail.com'),
('antonio.rossi@gmail.com'),
('davide.ferrari@gmail.com'),
('riccardo.verdi@gmail.com'),
('enrico.bianchi@gmail.com'),
('marco.rossi@gmail.com'),
('gabriele.ferrari@gmail.com'),
('michele.verdi@gmail.com'),
('andrea.bianchi@gmail.com'),
('luca.rossi@gmail.com');


INSERT INTO sport (nome_sport) VALUES ('Basket'), ('Pallavolo');

INSERT INTO societa_sportive (partita_iva, nome, indirizzo, responsabile) VALUES
('12345678901', 'Basket Club Trento Nord', 'Via Roma 1', 'paola.verdi@gmail.com'),
('23456789012', 'Pallavolo Trento Nord', 'Via Garibaldi 2', 'carlo.rossini@gmail.com'),
('34567890123', 'Basket Club Trento Sud', 'Via Vesuvio 3', 'elisa.ferrari@gmail.com'),
('45678901234', 'Pallavolo Trento Sud', 'Via Po 4', 'luigi.bianchi@gmail.com');

INSERT INTO squadre (nome, societa, sport) VALUES
('Basket Club Trento Nord', '12345678901', 'Basket'),
('Pallavolo Trento Nord', '23456789012', 'Pallavolo'),
('Basket Club Trento Sud', '34567890123', 'Basket'),
('Pallavolo Trento Sud', '45678901234', 'Pallavolo');


INSERT INTO allenatori_squadre (email_allenatore, id_squadra, data_inizio) VALUES
('vincenzo.italiano@gmail.com', (SELECT id FROM squadre WHERE nome = 'Basket Club Trento Nord' AND societa = 12345678901), (SELECT CURDATE())),
('raffaele.palladino@gmail.com', (SELECT id FROM squadre WHERE nome = 'Pallavolo Trento Nord' AND societa = 23456789012), (SELECT CURDATE())),
('giorgio.verdi@gmail.com', (SELECT id FROM squadre WHERE nome = 'Basket Club Trento Sud' AND societa = 34567890123), (SELECT CURDATE())),
('luisa.bianchi@gmail.com', (SELECT id FROM squadre WHERE nome = 'Pallavolo Trento Sud' AND societa = 45678901234), (SELECT CURDATE()));

INSERT INTO sessioni_registrazione(autore, data_ora_inizio, data_ora_fine) VALUES ('vincenzo.italiano@gmail.com', NOW(), NOW());

INSERT INTO video(locazione, nome, autore, nota, sessione) VALUES ('video/basket_test_1.mp4', 'Test Basket', 'vincenzo.italiano@gmail.com', NULL, (SELECT id FROM sessioni_registrazione WHERE autore = 'vincenzo.italiano@gmail.com' AND data_ora_inizio = NOW()));
INSERT INTO video(locazione, nome, autore, nota, sessione) VALUES ('video/volley_test_1.mp4', 'Test Volley', 'vincenzo.italiano@gmail.com', NULL, (SELECT id FROM sessioni_registrazione WHERE autore = 'vincenzo.italiano@gmail.com' AND data_ora_inizio = NOW()));

COMMIT;