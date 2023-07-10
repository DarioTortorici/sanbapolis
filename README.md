# Sanbapolis
Internship @ UniTN, business logic development of the management system of the sports facility located at Sanbartolameo

# Come utilizzarlo
## Prerequisiti
Per utilizzare il sistema bisogna avere apache, una database relazionale sql, wisenet server e composer

## Scaricarlo
Si può clonare l'intera repository direttamente nella propria cartella apache, solitamente `\var\www\html` oppure `xampp\htdocs` se si utilizza un ambiente in locale tramite xampp.

``` bash
git clone https://github.com/SportTech-UniTN/Sanbapolis.git
```

## Impostazioni esterne alla repository
È importante per assicurarsi che il sistema funzioni correttamente scaricare l'ultima versione del database ed impostare un _chronjob_ nel seguente modo:
1. aprire la tabella dei chronjob

``` bash
crontab -e
```
2. se è la prima volta che viene aperto, si deve scegliere un editor, potete scegliere quello che preferite senza vincoli
2. aggiungere il chronjob nel file appena aperto
``` bash
*/5 * * * * php /var/www/html/modals/ABAC.php
```

# API Reference
## Get
### get-events
| Action      | Descrizione                                                   | Parametro | Tipo Parametro |
|-------------|---------------------------------------------------------------|-----------|----------------|
| get-events  | Recupera tutti gli eventi dalla tabella `calendar_event`.      | -         | -              |

### get-event
| Action      | Descrizione                                                        | Parametro | Tipo Parametro |
|-------------|--------------------------------------------------------------------|-----------|----------------|
| get-event   | Recupera un evento specifico dalla tabella `calendar_event` dato un ID. | id        | string         |

### get-note
| Action      | Descrizione                                                                | Parametro | Tipo Parametro |
|-------------|----------------------------------------------------------------------------|-----------|----------------|
| get-note    | Recupera la descrizione di un evento dalla tabella `event_info` dato un ID. | id        | string         |

### get-coach-event
| Action            | Descrizione                                                                  | Parametro | Tipo Parametro |
|-------------------|------------------------------------------------------------------------------|-----------|----------------|
| get-coach-event   | Recupera tutti gli eventi dalla tabella `calendar_event` dove il coach allena. | coach     | string         |

### get-matches
| Action        | Descrizione                                                         | Parametro | Tipo Parametro |
|---------------|---------------------------------------------------------------------|-----------|----------------|
| get-matches   | Recupera tutti gli eventi segnati come match dalla tabella `calendar_event`. | -         | -              |

### get-event-info
| Action         | Descrizione                                                                | Parametro | Tipo Parametro |
|----------------|----------------------------------------------------------------------------|-----------|----------------|
| get-event-info | Recupera un evento specifico dalla tabella `event_info` dato un ID.         | id        | string         |

### get-cams
| Action     | Descrizione                                                          | Parametro | Tipo Parametro |
|------------|----------------------------------------------------------------------|-----------|----------------|
| get-cams   | Recupera le telecamere attive per un evento specifico dato un ID.     | id        | string         |

### get-time
| Action     | Descrizione                                                 | Parametro | Tipo Parametro |
|------------|-------------------------------------------------------------|-----------|----------------|
| get-time   | Recupera la data e l'ora di un evento dato un ID.             | id        | string         |


## Post
### save-event
| Action      | Descrizione                                                                           | Parametro | Tipo Parametro    |
|-------------|---------------------------------------------------------------------------------------|-----------|------------------|
| save-event  | Salva un nuovo evento nella tabella `calendar_event` e `event-info`.                   | groupId   | string (opzionale) |
|             |                                                                                       | allDay    | boolean (opzionale) |
|             |                                                                                       | startDate | string |
|             |                                                                                       | endDate   | string (opzionale) |
|             |                                                                                       | daysOfWeek | array (opzionale) |
|             |                                                                                       | startTime | string (opzionale) |
|             |                                                                                       | endTime   | string (opzionale) |
|             |                                                                                       | startRecur | string (opzionale) |
|             |                                                                                       | endRecur   | string (opzionale) |
|             |                                                                                       | url       | string (opzionale) |
|             |                                                                                       | society   | string |
|             |                                                                                       | sport     | string (opzionale) |
|             |                                                                                       | coach     | string |
|             |                                                                                       | note      | string (opzionale) |
|             |                                                                                       | eventType | string (opzionale) |

### Eliminare un evento
| Action        | Descrizione                                                     | Parametro | Tipo Parametro |
|---------------|-----------------------------------------------------------------|-----------|----------------|
| delete-event  | Elimina un evento dalla tabella `calendar_event` dato un ID.    | id        | string         |

### edit-event
| Action        | Descrizione                                                               | Parametro | Tipo Parametro |
|---------------|---------------------------------------------------------------------------|-----------|----------------|
| edit-event    | Modifica un evento esistente nella tabella `calendar_event` e `event-info`. | id        | string         |
|               |                                                                           | groupId   | string (opzionale) |
|               |                                                                           | startDate | string |
|               |                                                                           | endDate   | string (opzionale) |
|               |                                                                           | startTime | string (opzionale) |
|               |                                                                           | endTime   | string (opzionale) |
|               |                                                                           | url       | string (opzionale) |
|               |                                                                           | society   | string |
|               |                                                                           | coach     | string |
|               |                                                                           | note      | string (opzionale) |

### save-cams
| Action      | Descrizione                                                               | Parametro | Tipo Parametro |
|-------------|---------------------------------------------------------------------------|-----------|----------------|
| save-cams   | Salva le telecamere da attivare per un evento specifico.                   | id        | string         |
|             |                                                                           | cameras   | array          |
