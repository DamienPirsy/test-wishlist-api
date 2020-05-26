### setup

1) Clonare il repository
2) Scaricare i container MySql, Nginx e PHP:

```bash    
docker-compose up -d
```
L'applicativo è accessibile sulla porta :8084


### Migrazione iniziale:

```bash
cd test-wishlist-api/
~/test-wishlist-api: $ docker-compose exec app php artisan migrate:fresh --seed
```
Passando l'opzione `--seed` si può avere un primo popolamento di prodotti ed utenti, altrimenti è possibile crearli con i metodi REST previsti; 

### Unit testing

```bash
cd test-wishlist-api/
~/test-wishlist-api: $ docker-compose exec app vendor/bin/phpunit
```

### Swagger

Ho implementato un abbozzo di swagger con autenticazione per testare da web le varie rotte esposte. L'url è accesibile al path  ```/api/documentation```

### Comando di esportazione

Un comando artisan permette di esportare i dati delle wishlist in CSV usando una LOAD DATA INTO OUTFILE. I parametri opzionali sono:
* -f filename: specifica un nome del file; l'estensione .csv, insieme ad un timestamp, vengono aggiunte in automatico; il valore di default è "export_yyyymmddhhiiss.csv"
* - d dir: directory in cui viene salvato il file; di default è `/tmp`, quindi accessibile dalla bash del container relativo al database:
```bash
~/test-wishlist-api:$ docker-compose exec db bash
root@container-id: cd /tmp
```
* - H header: se presente viene mostrata l'intestazione del CSV

```bash
cd test-wishlist-api/
~/test-wishlist-api: $ docker-compose exec app php artisan wishlist:export [-f=export [-H] [-d=/tmp]
```