START TRANSACTION;

DROP DATABASE IF EXISTS sanbapolis;
CREATE DATABASE sanbapolis;
USE sanbapolis;

CREATE TABLE persone(
	id INTEGER AUTO_INCREMENT NOT NULL,
	email VARCHAR(320) NOT NULL,
	nome VARCHAR(64) NOT NULL,
	cognome VARCHAR(64) NOT NULL,
	data_nascita DATETIME,
	citta VARCHAR(64),
	indirizzo VARCHAR(64),
	telefono VARCHAR(64),
	digest_password VARCHAR(255) NOT NULL, /*password_hash() fuinzione php: "...it is recommended to store the result in a database column that can expand beyond 60 characters (255 characters would be a good choice)" */
	locazione_immagine_profilo VARCHAR(255),
	verificato BOOLEAN NOT NULL,
	data_ora_registrazione DATETIME NOT NULL,
	
	UNIQUE(email),
	PRIMARY KEY(id)
);

/*
--LA TABELLA UTENTI È STATA UNITA ALLA TABELLA PERSONA--
CREATE TABLE tipi_utenti(
	nome_tipo VARCHAR(64) NOT NULL,
	
	PRIMARY KEY(nome_tipo)
);
CREATE TABLE utenti(
	email VARCHAR(320) NOT NULL,
	digest_password VARCHAR(64) NOT NULL,
	tipo VARCHAR(64) NOT NULL,
	locazione_immagine_profilo VARCHAR(255),
	
	CONSTRAINT fk_persona_utente FOREIGN KEY (email) REFERENCES persone(email) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_nome_tipo FOREIGN KEY (tipo) REFERENCES tipi_utenti(nome_tipo) ON DELETE CASCADE ON UPDATE CASCADE,
	
	PRIMARY KEY(email)
);*/

/*Tipi di persone*/

CREATE TABLE tipi_allenatori (
	nome_tipo VARCHAR(64) PRIMARY KEY
);

CREATE TABLE allenatori(
	email VARCHAR(320) NOT NULL,
	tipo VARCHAR(64) NOT NULL,
	privilegi_cam BOOLEAN NOT NULL,

	CONSTRAINT fk_email_allenatore FOREIGN KEY (email) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_tipo_allenatore FOREIGN KEY (tipo) REFERENCES tipi_allenatori(nome_tipo) ON UPDATE CASCADE ON DELETE CASCADE,
	
	PRIMARY KEY(email)
);

CREATE TABLE tifosi(
	email VARCHAR(320) NOT NULL,
	
	CONSTRAINT fk_email_tifoso FOREIGN KEY (email) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	
	PRIMARY KEY(email)
);

CREATE TABLE giocatori(
	email VARCHAR(320) NOT NULL,
	
	CONSTRAINT fk_email_giocatore FOREIGN KEY (email) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	
	PRIMARY KEY(email)
);

CREATE TABLE manutentori(
	email VARCHAR(320) NOT NULL,
	
	CONSTRAINT fk_email_manutentore FOREIGN KEY (email) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	
	PRIMARY KEY(email)
);


CREATE TABLE tag_rfid(
	id INTEGER PRIMARY KEY
);

CREATE TABLE sessioni_registrazione (
	id INTEGER AUTO_INCREMENT NOT NULL,
	autore VARCHAR(320) NOT NULL,
	data_ora_inizio DATETIME NOT NULL,
	data_ora_fine DATETIME NOT NULL,

	CONSTRAINT fk_autore_sessione FOREIGN KEY (autore) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE (autore, data_ora_inizio),
	PRIMARY KEY (id)
);

CREATE TABLE video(
	id INTEGER AUTO_INCREMENT NOT NULL,
	locazione VARCHAR(255) NOT NULL, /*255 in teoria lunghezza massima per una path in linux*/
	nome VARCHAR(64) NOT NULL,
	autore VARCHAR(64) NOT NULL,
	nota TEXT,
	sessione INTEGER NOT NULL,
	
	CONSTRAINT fk_email_autore FOREIGN KEY (autore) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_video_sessione FOREIGN KEY (sessione) REFERENCES sessioni_registrazione(id) ON UPDATE CASCADE ON DELETE CASCADE,
	
	UNIQUE(locazione),
	PRIMARY KEY(id)
);

CREATE TABLE clips_video (
	locazione_video_originale VARCHAR(255) NOT NULL,
	locazione_clip VARCHAR(255) NOT NULL,

	CONSTRAINT fk_video_originale FOREIGN KEY (locazione_video_originale) REFERENCES video(locazione) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_clip_video FOREIGN KEY (locazione_clip) REFERENCES video(locazione) ON UPDATE CASCADE ON DELETE CASCADE,

	PRIMARY KEY(locazione_video_originale, locazione_clip)
);


CREATE TABLE screenshots (
	id INTEGER AUTO_INCREMENT NOT NULL,
	locazione VARCHAR(255) NOT NULL,
	nome VARCHAR(64)  NOT NULL,
	video VARCHAR(255) NOT NULL,
	nota TEXT,

	CONSTRAINT fk_screenshot_video FOREIGN KEY (video) REFERENCES video(locazione) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE (locazione),
	PRIMARY KEY (id)
);

CREATE TABLE segnaposti (
	id INTEGER AUTO_INCREMENT NOT NULL,
	minutaggio TIME(3) NOT NULL,
	video VARCHAR(255) NOT NULL,
	nome VARCHAR(64),
	nota TEXT,

	CONSTRAINT fk_segnaposti_video FOREIGN KEY (video) REFERENCES video(locazione) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE(minutaggio, video),
	PRIMARY KEY (id)
);

CREATE TABLE societa_sportive(
	partita_iva VARCHAR(11) NOT NULL,
	nome VARCHAR(64) NOT NULL, 
	indirizzo VARCHAR(64),
	responsabile VARCHAR(320) NOT NULL,
	
	CONSTRAINT fk_email_responsabile FOREIGN KEY (responsabile) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	
	PRIMARY KEY(partita_iva)
);

CREATE TABLE sport(
	nome_sport VARCHAR(64) PRIMARY KEY
);

CREATE TABLE squadre(
	id INTEGER AUTO_INCREMENT NOT NULL,
	nome VARCHAR(64) NOT NULL,
	societa VARCHAR(11) NOT NULL,
	sport VARCHAR(64) NOT NULL,
	/*codice_accesso VARCHAR(6) NOT NULL,*/
	
	CONSTRAINT fk_sport_squadra FOREIGN KEY (sport) REFERENCES sport(nome_sport) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_squadra_societa FOREIGN KEY (societa) REFERENCES societa_sportive(partita_iva) ON UPDATE CASCADE ON DELETE CASCADE,
	
	UNIQUE(nome, societa),
	PRIMARY KEY(id)
);

CREATE TABLE allenatori_squadre(
	id INTEGER AUTO_INCREMENT NOT NULL,
	email_allenatore VARCHAR(320) NOT NULL,
	id_squadra INTEGER NOT NULL,
	data_inizio DATETIME NOT NULL,
	data_fine DATETIME,
	
	CONSTRAINT fk_email_allenatore_squadra FOREIGN KEY (email_allenatore) REFERENCES allenatori(email) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_squadra_allenatore FOREIGN KEY (id_squadra) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	
	UNIQUE (data_inizio, email_allenatore, id_squadra),
	PRIMARY KEY(id)	
);

CREATE TABLE giocatori_squadre (
	id INTEGER AUTO_INCREMENT NOT NULL,
	email_giocatore VARCHAR(320) NOT NULL,
	id_squadra INTEGER NOT NULL,
	data_inizio DATETIME NOT NULL,
	data_fine DATETIME,
	
	CONSTRAINT fk_email_giocatore_squadra FOREIGN KEY (email_giocatore) REFERENCES giocatori(email) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_squadra_giocatore FOREIGN KEY (id_squadra) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	
	UNIQUE (data_inizio, email_giocatore, id_squadra),
	PRIMARY KEY(id)	
);

/*tabella che serve per gestire il calendario per il framework*/
CREATE TABLE calendar_events (
	id INT AUTO_INCREMENT, /*problema di integrità: potrei avere id diversi con l'auto increment ma il resto dei valori tutti uguali*/
	groupId INT,
	allDay BOOLEAN,
	start DATETIME,
	end DATETIME,
	daysOfWeek TEXT,
	startTime TIME,
	endTime TIME,
	startRecur DATE,
	endRecur DATE,
	title VARCHAR(255),
	url VARCHAR(255),
	interactive BOOLEAN,
	className VARCHAR(255),
	editable BOOLEAN,
	startEditable BOOLEAN,
	durationEditable BOOLEAN,
	resourceEditable BOOLEAN,
	resourceId VARCHAR(255),
	resourceIds TEXT,
	display VARCHAR(20),
	overlap BOOLEAN,
	color VARCHAR(20),
	backgroundColor VARCHAR(20),
	borderColor VARCHAR(20),
	textColor VARCHAR(20),

	UNIQUE(title, start), /*per risolvere i problemi di integrità, unique titolo, inizio dell'evento;
	si è deciso quindi che non si potranno avere eventi che iniziano lo stesso giorno, la stessa ora e con lo stesso titolo*/

	PRIMARY KEY (id)
);

CREATE TABLE prenotazioni (
	id INTEGER AUTO_INCREMENT NOT NULL,
	autore_prenotazione VARCHAR(320) NOT NULL,
	data_ora_inizio DATETIME NOT NULL,
	data_ora_fine DATETIME NOT NULL,
	/*sport VARCHAR(64) NOT NULL, serve? lo posso ricavare dalla squadra*/
	id_squadra INTEGER NOT NULL,
	id_calendar_events INTEGER NOT NULL,
	nota TEXT,
	
	CONSTRAINT fk_email_autore_prenotazione FOREIGN KEY (autore_prenotazione) REFERENCES persone(email) ON UPDATE CASCADE ON DELETE CASCADE,
	/*CONSTRAINT fk_sport_prenotazione FOREIGN KEY (sport) REFERENCES sport(nome_sport) ON UPDATE CASCADE ON DELETE CASCADE,*/
	CONSTRAINT fk_squadra_prenotazione FOREIGN KEY (id_squadra) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_prenotazione_evento_calendario FOREIGN KEY (id_calendar_events) REFERENCES calendar_events(id) ON UPDATE CASCADE ON DELETE CASCADE,
	
	UNIQUE (autore_prenotazione, data_ora_inizio, data_ora_fine),
	PRIMARY KEY (id)
);

CREATE TABLE partite (
	id INTEGER AUTO_INCREMENT NOT NULL,
	id_squadra_casa INTEGER NOT NULL,
	id_squadra_trasferta INTEGER NOT NULL,
	data_ora_inizio DATETIME NOT NULL,
	data_ora_fine DATETIME,
	sport VARCHAR(64) NOT NULL,
	prenotazione INTEGER NOT NULL,

	CONSTRAINT fk_squadra_casa FOREIGN KEY (id_squadra_casa) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_squadra_trasferta FOREIGN KEY (id_squadra_trasferta) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_sport_partita FOREIGN KEY (sport) REFERENCES sport(nome_sport) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_prenotazione_partita FOREIGN KEY (prenotazione) REFERENCES prenotazioni(id) ON UPDATE CASCADE ON DELETE CASCADE,


	UNIQUE(id_squadra_casa, id_squadra_trasferta, data_ora_inizio),
	PRIMARY KEY (id)

);

CREATE TABLE allenamenti ( 
	id INTEGER AUTO_INCREMENT NOT NULL,
	id_squadra INTEGER NOT NULL,
	data_ora_inizio DATETIME NOT NULL,
	data_ora_fine DATETIME NOT NULL,
	prenotazione INTEGER NOT NULL,
	
	CONSTRAINT fk_allenamento_squadra FOREIGN KEY (id_squadra) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_prenotazione_allenamento FOREIGN KEY (prenotazione) REFERENCES prenotazioni(id) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE (id_squadra, data_ora_inizio),
	PRIMARY KEY(id)
);

CREATE TABLE formazioni (
	id INTEGER AUTO_INCREMENT NOT NULL,
	id_squadra INTEGER NOT NULL,
	id_partita INTEGER NOT NULL,

	CONSTRAINT fk_formazione_squadra FOREIGN KEY (id_squadra) REFERENCES squadre(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_formazione_partita FOREIGN KEY (id_partita) REFERENCES partite(id) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE (id_squadra, id_partita),
	PRIMARY KEY (id)
);

/*tabella che associa una prenotazione al relativo allenamento -NON USATA PER ORA- PER ORA CHIAVE ESTERNA DA ALLENAMENTI A PRENOTAZIONI
CREATE TABLE prenotazioni_allenamenti (
	id_prenotazione INTEGER NOT NULL,
	id_allenamento INTEGER NOT NULL,

	CONSTRAINT fk_prenotazione_allenamento_pren FOREIGN KEY (id_prenotazione) REFERENCES prenotazioni(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_prenotazione_allenamento_allen FOREIGN KEY (id_allenamento) REFERENCES allenamenti(id) ON UPDATE CASCADE ON DELETE CASCADE,

	PRIMARY KEY (id_prenotazione, id_allenamento)
);*/

/*tabella che associa una prenotazione alla relativa partita -NON USATA PER ORA- PER ORA CHIAVE ESTERNA DA PARTITE A PRENOTAZIONI
CREATE TABLE prenotazioni_partite (
	id_prenotazione INTEGER NOT NULL,
	id_partita INTEGER NOT NULL,

	CONSTRAINT fk_prenotazione_partita_pren FOREIGN KEY (id_prenotazione) REFERENCES prenotazioni(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_prenotazione_partita_part FOREIGN KEY (id_partita) REFERENCES partite(id) ON UPDATE CASCADE ON DELETE CASCADE,

	PRIMARY KEY (id_prenotazione, id_partita)
);*/

/*giocatori che fanno parte di una formazione*/
CREATE TABLE formazioni_giocatori (
	id INTEGER AUTO_INCREMENT NOT NULL,
	id_formazione INTEGER NOT NULL,
	giocatore VARCHAR(320) NOT NULL,
	titolare BOOLEAN,
	minuto_ingresso DATETIME, /*null se non subentra*/
	minuto_uscita DATETIME, /*null se non esce prima della partita*/
	tag_giocatore INTEGER NOT NULL,

	CONSTRAINT fk_giocatore_formazione_giocatore FOREIGN KEY (giocatore) REFERENCES giocatori(email) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_formazione_formazione_giocatore FOREIGN KEY (id_formazione) REFERENCES formazioni(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_partita_giocatore_tag FOREIGN KEY (tag_giocatore) REFERENCES tag_rfid(id) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE (id_formazione, giocatore),
	PRIMARY KEY (id)
);

CREATE TABLE allenamenti_giocatori (
	id INTEGER AUTO_INCREMENT NOT NULL,
	id_allenamento INTEGER NOT NULL,
	giocatore VARCHAR(320) NOT NULL,
	tag_giocatore INTEGER NOT NULL,

	CONSTRAINT fk_giocatore_allenamento_giocatore FOREIGN KEY (giocatore) REFERENCES giocatori(email) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_allenameno_allenamento_giocatore FOREIGN KEY (id_allenamento) REFERENCES allenamenti(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_allenamento_giocatore_tag FOREIGN KEY (tag_giocatore) REFERENCES tag_rfid(id) ON UPDATE CASCADE ON DELETE CASCADE,

	UNIQUE (id_allenamento, giocatore),
	PRIMARY KEY (id)
);


CREATE TABLE inviti_allenatori (
	email VARCHAR(320) PRIMARY KEY
);

CREATE TABLE inviti_giocatori (
	email VARCHAR(320) PRIMARY KEY
);

CREATE TABLE telecamere (
	id INTEGER NOT NULL,
	indirizzo_ipv4 VARCHAR(15),
	indirizzo_ipv6 VARCHAR(39),
	
	UNIQUE(indirizzo_ipv4, indirizzo_ipv6),	
	PRIMARY KEY (id)
);

CREATE TABLE telecamere_prenotazioni (
	telecamera INTEGER NOT NULL,
	prenotazione INTEGER NOT NULL,
	
	CONSTRAINT fk_telecamera_prenotazione FOREIGN KEY (telecamera) REFERENCES telecamere(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_prenotazione_telecamera FOREIGN KEY (prenotazione) REFERENCES prenotazioni(id) ON UPDATE CASCADE ON DELETE CASCADE,
	
	PRIMARY KEY (telecamera, prenotazione)
);


COMMIT;