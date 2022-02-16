
Luca Albertini Mat. 287744

WeGiveSupport
=======
Progetto PDGT | Sistema di supporto ICT con API in paradigma RESTful

Il servizio
------------

In ambiente ICT è di fondamentale importanza tenere sotto controllo le richieste di assistenza che vengono inoltrate.
Il mio progetto di PDGT ha come scopo proprio l'organizzazione di queste richieste.
Attraverso il servizio, ogni agente (tecnico ICT) ha la possibilità di richiedere sui tickets le seguenti operazioni: 
- inserimento
- modifica
- eliminazione
- ricerca con filtri [status, priorità] che se assenti restituiscono l'intera lista tickets

Per una maggiore sicurezza sulle operazioni, ogni agente è fornito di username e password, che serviranno poi ad ottenere l'autenticazione.
Non è prevista un'utenza amministrativa in grado di svolgere funzioni con privilegi elevati (come inserire nuovi agenti e clienti).
Dunque, per semplicità, ogni agente può inoltre:
- modificare se stesso ed altri agenti
- ottenere una lista dei ticket assegnati a se stesso e agli altri agenti
- inserire, modificare, eliminare clienti e di questi ultimi ottenerne la lista completa
- ottenere una lista dei ticket generati dai vari clienti

Architettura
------------

Il servizio è sviluppato interamente con la versione *OOP di PHP* (PDO) *v7.4* ed utilizza un database *MySQL* per lo storage dei dati.
MySQLha una nativa compatibilità con PHP e rappresenta quindi un'ottima soluzione come base dati per questo servizio.
La scelta della versione ad oggetti di PHP, invece, non ha un fondamento implementativo, nasce dalla mia volontà di voler continuare ad utilizzare OOP e dalla più facile modifica futura del codice.

L'autenticazione degli agenti è realizzata mediante lo standard *Basic Authentication* con username e password.
Le password, che sono memorizzate (ma offuscate) nel database, vengono generate dalla funzione standard PHP [password_hash](https://www.php.net/manual/en/function.password-hash.php) che esegue l'hash della password in chiaro combinata con un *salt* generato randomicamente.
Ad ogni login, poi, la complementare funzione [password_verify](https://www.php.net/manual/en/function.password-verify.php) controlla le corrispondenze degli hash e consente o meno l'accesso all'agente.
Ad accesso consentito viene generato un token di sessione *JWT* per evitare l'inserimento continuativo delle credenziali per ogni richiesta HTTP eseguita.
Il supporto all'encoding e decoding dei token JWT è frutto dell'utilizzo della libreria [PHP-JWT](https://github.com/firebase/php-jwt).
@Grazie [@Firebase](https://github.com/firebase)!

Per ottenere delle *friendly url* è stato utilizzato il file di configurazione .htaccess con diverse regole di url rewrite.
Due esempi:
```bash
RewriteRule ^api/tickets/(.*)?$ api/tickets.php?id=$1
```
Con questa regola un url uguale a *https://wegivesupport.net/api/tickets/3* viene redirezionato ed interpretato dal servizio come *https://wegivesupport.net/api/tickets.php?id=3* andando a restituire poi le informazioni del ticket con id 3 (in caso di GET).

```bash
RewriteRule ^api/customers/(.*)?/tickets$ api/tickets.php?customer=$1
```
Per restituire l'elenco dei ticket generati da uno specifico cliente, invece, viene utilizzata la regola di rewrite definita poco sopra.

*N.B. per essere più conformi possibile allo standard RESTful ed "eliminare" le estensioni dei file al fine di ottenere url "trasparenti" dalla tecnologia implementativa è stata utilizzata una terza regola di rewrite*

Documentazione
------------

Di seguito uno schema riassuntivo degli endpoint del servizio e richieste supportate:
```bash
- api/auth
    - POST	    -> validazione login agente ICT con generazione token di sessione JWT valido 60 minuti
	
- api/tickets
	- POST      -> inserimento di un nuovo ticket

- api/tickets?status=X&priority=Y
	- GET       -> ricerca tickets con filtri [status, priorità] che, se assenti, restituiscono una lista completa dei tickets presenti
	
- api/tickets/:id
	- GET       -> visualizzazione delle informazioni di uno specifico ticket
	- PUT       -> modifica delle informazioni di uno specifico ticket
	- DELETE    -> rimozione di uno specifico ticket
```
```bash
- api/customers
    - GET       -> restituzione della lista dei clienti presenti
	- POST      -> inserimento di un nuovo cliente
	
- api/customer/:id
	- GET       -> visualizzazione delle informazioni di uno specifico cliente		 
	- PUT       -> modifica delle informazioni di uno specifico cliente
	- DELETE 	-> rimozione di uno specifico cliente
	
- api/customer/:id/tickets
	- GET       -> restituzione della lista dei tickets generati da uno specifico cliente
```
```bash
- api/agents
	- GET       -> restituzione della lista degli agenti ICT presentis

- api/agents/:id
	- GET 	    -> visualizzazione delle informazioni di uno specifico agente
	- PUT 	    -> modifica delle informazioni di uno specifico agente (*no password e salt*)
	
- api/agents/:id/tickets
	- GET	    -> restituzione della lista dei tickets assegnati ad uno specifico agente
```
------------
Le risorse restituite dal servizio sono tutte in codifica standard *JSON* poichè rappresenta una soluzione solida ed intuitiva alla formattazione dei dati di input e di output per HTTP.
Di seguito il formato in standard JSON di un ticket:
```bash
    {
            "id": "00003",
            "opening_date": "2022-02-12 23:31:48",
            "closing_date": null,
            "customer": "0003",
            "agent": "003",
            "priority": "001",
            "status": "001",
            "object": "Add firewall NAT policy for expose webserver in WWW",
            "message": "",
            "direct link": "/api/tickets/00003"
    }
```
Il campo *closing_date* è volutamente null in quanto all'atto della creazione viene impostato a null il valore datetime della data di chiusura.
Il campo *priority* può assumere i valori 1 - 2 - 3 a seconda che si tratti di un ticket a bassa, media o alta priorità.
Lo *status* di un ticket può assumere anch'esso i valori 1 - 2 - 3 rispettivamente per aperto, in sospeso o risolto.
Il link diretto permette, in caso di restituzione di una lista di risorse di accedere alla specifica risorsa (mediante GET, PUT o DELETE in base all'operazione desiderata)

*Campi obbligatori*
- ticket:
    POST e PUT:  {customer, agent, priority, object}
- agent:
    PUT:         {agent_name, username, email}
- customer:
    POST e PUT:  {company, ref_emanil, ref_name}

Casi d'uso
------------
[WIKI](https://github.com/Liuc-0392/WeGiveSupport/wiki/Caso-d'uso-standard)
