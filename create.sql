CREATE DATABASE gestionale;
create table Gruppi_cassa
(
  id int auto_increment
    primary key,
  `desc` varchar(15) default 'Non_Definito' not null,
  icon varchar(64) null,
  constraint Gruppi_cassa_id_uindex
  unique (id),
  constraint Gruppi_cassa_desc_uindex
  unique (`desc`)
)
  engine=InnoDB
;

create table magazzino
(
  id int auto_increment
    primary key,
  Nome varchar(32) not null,
  Descrizione varchar(128) null,
  Prezzo float not null,
  InGiacenza int not null,
  gruppo varchar(15) default 'Non_Definito' null,
  updated tinyint(1) default '0' null,
  constraint magazzino_id_uindex
  unique (id),
  constraint magazzino_Nome_uindex
  unique (Nome),
  constraint magazzino_Gruppi_cassa_desc_fk
  foreign key (gruppo) references Gruppi_cassa (`desc`)
    on update cascade on delete set null
)
  engine=InnoDB
;

create index magazzino_Gruppi_cassa_desc_fk
  on magazzino (gruppo)
;

create table order_status
(
  id varchar(20) not null
    primary key,
  constraint order_status_id_uindex
  unique (id)
)
  engine=InnoDB
;

create table privilegi
(
  id int auto_increment
    primary key,
  descrizione text not null,
  constraint privilegi_id_uindex
  unique (id)
)
  engine=InnoDB
;

create table funzioni
(
  id int auto_increment
    primary key,
  Servizio varchar(64) not null,
  Subheader varchar(64) null,
  Icon varchar(64) null,
  RequiredPriviledge int not null,
  Gruppo varchar(32) null,
  Destination varchar(64) null,
  isALink tinyint(1) default '1' not null,
  constraint funzioni_id_uindex
  unique (id),
  constraint funzioni_Servizio_uindex
  unique (Servizio),
  constraint funzioni_privilegi_id_fk
  foreign key (RequiredPriviledge) references privilegi (id)
)
  engine=InnoDB
;

create index funzioni_privilegi_id_fk
  on funzioni (RequiredPriviledge)
;

create table proprieta
(
  prop varchar(32) not null
    primary key,
  valore varchar(64) not null,
  constraint table_name_prop_uindex
  unique (prop)
)
  engine=InnoDB
;

create table utenti
(
  id int auto_increment
    primary key,
  username varchar(32) not null,
  password varchar(65) not null,
  new tinyint(1) default '1' not null,
  constraint utenti_id_uindex
  unique (id),
  constraint utenti_username_uindex
  unique (username)
)
  engine=InnoDB
;

create table Ordini
(
  id int auto_increment
    primary key,
  orderID varchar(64) not null,
  productID int not null,
  quantita int not null,
  timestamp mediumtext null,
  stato varchar(20) default 'IN_CORSO' null,
  orderNo varchar(30) not null,
  incrementalNo int not null,
  user int null,
  totale float not null,
  constraint Ordini_id_uindex
  unique (id),
  constraint Ordini_utenti_id_fk
  foreign key (user) references utenti (id)
)
  engine=InnoDB
;

create index Ordini_magazzino_id_fk
  on Ordini (productID)
;

create index Ordini_order_status_id_fk
  on Ordini (stato)
;

create index Ordini_utenti_id_fk
  on Ordini (user)
;

create table assegnazionePrivilegi
(
  ID int auto_increment
    primary key,
  previlegeID int not null,
  userID int not null,
  constraint assegnazionePrivilegi_ID_uindex
  unique (ID),
  constraint assegnazionePrivilegi_privilegi_id_fk
  foreign key (previlegeID) references privilegi (id),
  constraint assegnazionePrivilegi_utenti_id_fk
  foreign key (userID) references utenti (id)
)
  engine=InnoDB
;

create index assegnazionePrivilegi_privilegi_id_fk
  on assegnazionePrivilegi (previlegeID)
;

create index assegnazionePrivilegi_utenti_id_fk
  on assegnazionePrivilegi (userID)
;

create table keyring_sessions
(
  sessionID varchar(129) not null
    primary key,
  randomKey varchar(40) not null,
  userID int null,
  logged tinyint(1) default '0' not null,
  sessid varchar(40) null,
  validUntil mediumtext not null,
  used tinyint(1) default '0' not null,
  constraint keyring_sessions_sessionID_uindex
  unique (sessionID),
  constraint keyring_sessions_utenti_id_fk
  foreign key (userID) references utenti (id)
)
  engine=InnoDB
;

create index keyring_sessions_utenti_id_fk
  on keyring_sessions (userID)
;

INSERT INTO gestionale.Gruppi_cassa (id, `desc`, icon) VALUES (4, 'CUCINA', 'sap-icon://basket');
INSERT INTO gestionale.Gruppi_cassa (id, `desc`, icon) VALUES (5, 'BAR', 'sap-icon://lab');

INSERT INTO gestionale.privilegi (id, descrizione) VALUES (1, 'utenti');
INSERT INTO gestionale.privilegi (id, descrizione) VALUES (2, 'cassa');
INSERT INTO gestionale.privilegi (id, descrizione) VALUES (3, 'impostazioni');
INSERT INTO gestionale.privilegi (id, descrizione) VALUES (4, 'inventario');
INSERT INTO gestionale.privilegi (id, descrizione) VALUES (5, 'ordini');
INSERT INTO gestionale.privilegi (id, descrizione) VALUES (6, 'database');
INSERT INTO gestionale.privilegi (id, descrizione) VALUES (7, 'stampa');

INSERT INTO gestionale.proprieta (prop, valore) VALUES ('bill_save_folder', 'saved_bills');
INSERT INTO gestionale.proprieta (prop, valore) VALUES ('Giacency_update_interval', '10000');
INSERT INTO gestionale.proprieta (prop, valore) VALUES ('hostName', '192.168.1.46/cassa/');
INSERT INTO gestionale.proprieta (prop, valore) VALUES ('Order_update_internal', '3000');
INSERT INTO gestionale.proprieta (prop, valore) VALUES ('printer_name', 'Samsung-C460');
INSERT INTO gestionale.proprieta (prop, valore) VALUES ('theme', 'sap_bluecrystal');

INSERT INTO gestionale.utenti (id, username, password, new) VALUES (1, 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 0);

INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (1, 1);
INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (2, 1);
INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (3, 1);
INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (4, 1);
INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (5, 1);
INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (6, 1);
INSERT INTO gestionale.assegnazionePrivilegi(previlegeID, userID) VALUES (7, 1);

INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (1, 'Gestione Inventario', null, 'sap-icon://add-product', 4, 'Magazzino e gestione', '/cassa/sap/Inventory/inventory.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (2, 'Storico ordini', 'Visualizza lo storico degli ordini', 'sap-icon://customer-history', 5, 'Magazzino e gestione', '/cassa/sap/History/history.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (3, 'Cassa rapida', 'Cassa a pannelli adatta ad un terminale touch', 'sap-icon://grid', 2, 'Cassa e servizio', '/cassa/sap/Cassa_Tiled/cassa.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (4, 'Cassa standard', null, 'sap-icon://money-bills', 2, 'Cassa e servizio', '/cassa/sap/Cassa/cassa.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (5, 'Andamenti', 'Statistiche varie', 'sap-icon://area-chart', 5, 'Magazzino e gestione', '/cassa/sap/Analytics/charts.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (6, 'Amministrazione utenti', null, 'sap-icon://account', 1, 'Sistema e Amministrazione', '/cassa/sap/Administration/users.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (7, 'Billing', null, null, 7, '---', '---', 0);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (8, 'Giacenze', null, null, 2, '---', '---', 0);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (9, 'SQLConsole', 'Gestione del database MySQL', 'sap-icon://user-settings', 6, 'Sistema e Amministrazione', '/cassa/sap/Dashboard/secure/mysql-console.php', 1);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (10, 'Aggiorna magazzino', null, null, 6, '---', '---', 0);
INSERT INTO gestionale.funzioni (id, Servizio, Subheader, Icon, RequiredPriviledge, Gruppo, Destination, isALink) VALUES (11, 'Crea utente', null, null, 6, '---', '---', 0);