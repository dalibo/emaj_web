<?php

	/**
	* French Language file for Emaj_web.
	*
	*/

	// Language and character set
	$lang['applocale'] = 'fr-FR';
	$lang['applocalearray'] = array('fr-FR','fr_FR','fr','fra','fr_FR@euro','fr_FR.utf8');
	$lang['applangdir'] = 'ltr';
	// Php format for timestamp fields, distinguishing the format for:
	// - the old times, producing something like '23 Jun 2020 12:34:56'
	$lang['stroldtimestampformat'] = '%d %b %Y %H:%M:%S';
	// - the recent times, producing something like 'Mon 23 Jun 12:34:56'
	$lang['strrecenttimestampformat'] = '%a %d %b %H:%M:%S';
	// - the timestamp abbreviated into precise time
	$lang['strprecisetimeformat'] = '%H:%M:%S.%µ';
	// Internal format for full interval display
	$lang['strintervalformat'] = 'DD j HH h MM min SS.US s';

	// Welcome
	$lang['strintro'] = 'Bienvenue dans %s %s, le client web pour';
	$lang['strlink'] = 'Quelques liens :';
	$lang['strpgsqlhome'] = 'Page d\'accueil de PostgreSQL';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Documentation E-Maj';
	$lang['stremajdoc_url'] = 'http://emaj.readthedocs.io/fr/latest/';
	$lang['stremajproject'] = 'E-Maj sur github';
	$lang['stremajwebproject'] = 'Emaj_web sur github';

	// Basic strings
	$lang['strlogin'] = 'Connexion';
	$lang['strloginfailed'] = 'Échec de la connexion';
	$lang['strlogindisallowed'] = 'Connexion désactivée pour raison de sécurité';
	$lang['strserver'] = 'Serveur';
	$lang['strservers'] = 'Serveurs';
	$lang['strconfiguredservers'] = 'Serveurs PostgreSQL';
	$lang['strgroupservers'] = 'Serveurs PostgreSQL du groupe "%s"';
	$lang['strallservers'] = 'Tous les serveurs';
	$lang['strintroduction'] = 'Introduction';
	$lang['strhost'] = 'Hôte';
	$lang['strport'] = 'Port';
	$lang['strlogout'] = 'Déconnexion';
	$lang['strowner'] = 'Propriétaire';
	$lang['straction'] = 'Action';
	$lang['stractions'] = 'Actions';
	$lang['strname'] = 'Nom';
	$lang['strproperties'] = 'Propriétés';
	$lang['strbrowse'] = 'Parcourir';
	$lang['strdrop'] = 'Supprimer';
	$lang['strnotnull'] = 'NOT NULL';
	$lang['strprev'] = 'Précédent';
	$lang['strnext'] = 'Suivant';
	$lang['strfirst'] = '<< Début';
	$lang['strlast'] = 'Fin >>';
	$lang['strcreate'] = 'Créer';
	$lang['strcomment'] = 'Commentaire';
	$lang['strcommentlabel'] = 'Commentaire : ';
	$lang['strdefault'] = 'Défaut';
	$lang['strok'] = 'OK';
	$lang['strcancel'] = 'Annuler';
	$lang['strreset'] = 'Réinitialiser';
	$lang['strdelete'] = 'Effacer';
	$lang['strupdate'] = 'Modifier';
	$lang['stryes'] = 'Oui';
	$lang['strno'] = 'Non';
	$lang['strtrue'] = 'TRUE';
	$lang['strfalse'] = 'FALSE';
	$lang['strcolumn'] = 'Colonne';
	$lang['strcolumns'] = 'Colonnes';
	$lang['strrows'] = 'ligne(s)';
	$lang['strrowsaff'] = 'ligne(s) affectée(s).';
	$lang['strback'] = 'Retour';
	$lang['strqueryresults'] = 'Résultats de la requête';
	$lang['strencoding'] = 'Codage';
	$lang['strsql'] = 'SQL';
	$lang['strexecute'] = 'Lancer';
	$lang['strconfirm'] = 'Confirmer';
	$lang['strellipsis'] = '...';
	$lang['strexpand'] = 'Étendre';
	$lang['strcollapse'] = 'Réduire';
	$lang['strrefresh'] = 'Rafraichir';
	$lang['strdownload'] = 'Télécharger';
	$lang['strexport'] = 'Exporter';
	$lang['strimport'] = 'Importer';
	$lang['stropen'] = 'Ouvrir';
	$lang['strruntime'] = 'Temps d\'exécution total : %s ms';
	$lang['strpaginate'] = 'Paginer les résultats';
	$lang['strtrycred'] = 'Utilisez ces identifiants pour tous les serveurs';
	$lang['strconfdropcred'] = 'Par mesure de sécurité, la déconnexion supprimera le partage de vos identifiants pour tous les serveurs. Êtes-vous certain de vouloir vous déconnecter ?';
	$lang['strstart'] = 'Démarrer';
	$lang['strstop'] = 'Arrêter';
	$lang['strgotoppage'] = 'Haut de la page';
	$lang['strselect'] = 'Sélectionner';
	$lang['stractionsonselectedobjects'] = 'Actions sur les objets (%s)';
	$lang['strall'] = 'Tous';
	$lang['strnone'] = 'Aucun';
	$lang['strinvert'] = 'Inverser';
	$lang['emajnotassigned'] = 'Non affectés';
	$lang['strlevel'] = 'Niveau';
	$lang['strmessage'] = 'Message';
	$lang['strbegin'] = 'Début';
	$lang['strend'] = 'Fin';
	$lang['strsince'] = 'Depuis';
	$lang['strquantity'] = 'Quantité';

	// User-supplied SQL editing
	$lang['strsqledit'] = 'Edition de requête SQL';
	$lang['strsearchpath'] = 'Chemin de recherche des schémas ';

	// User-supplied SQL history
	$lang['strhistory'] = 'Historique';
	$lang['strsqlhistory'] = 'Historique des requêtes SQL';
	$lang['strnohistory'] = 'Pas d\'historique.';
	$lang['strclearhistory'] = 'Effacer l\'historique';
	$lang['strdelhistory'] = 'Supprimer de l\'historique';
	$lang['strconfdelhistory'] = 'Voulez-vous vraiment supprimer cette requête de l\'historique ?';
	$lang['strconfclearhistory'] = 'Voulez-vous vraiment effacer l\'historique ?';
	$lang['strnodatabaseselected'] = 'Veuillez sélectionner une base de données.';

	// Database Sizes
	$lang['strnoaccess'] = 'Pas d\'Accès'; 
	$lang['strsize'] = 'Taille';
	$lang['strbytes'] = 'octets';
	$lang['strkb'] = ' Ko';
	$lang['strmb'] = ' Mo';
	$lang['strgb'] = ' Go';
	$lang['strtb'] = ' To';

	// Error handling
	$lang['strnotloaded'] = 'Vous n\'avez pas compilé correctement le support de la base de données dans votre installation de PHP.';
	$lang['strpostgresqlversionnotsupported'] = 'Cette version de PostgreSQL n\'est pas supportée. Merci de mettre à jour PHP à la version %s ou ultérieure.';
	$lang['strbadschema'] = 'Schéma spécifié invalide.';
	$lang['strsqlerror'] = 'Erreur SQL :';
	$lang['strinstatement'] = 'Dans l\'instruction :';
	$lang['strnodata'] = 'Pas de résultats.';
	$lang['strnoobjects'] = 'Aucun objet trouvé.';
	$lang['strcannotdumponwindows'] = 'La sauvegarde de table complexe et des noms de schémas n\'est pas supporté sur Windows.';
	$lang['strinvalidserverparam'] = 'Tentative de connexion avec un serveur invalide, il est possible que quelqu\'un essai de pirater votre système.'; 
	$lang['strnoserversupplied'] = 'Aucun serveur fournis !';
	$lang['strconnectionfail'] = 'Connexion au serveur échouée.';
	$lang['strimporterror-uploadedfile'] = 'Erreur d\'importation : le fichier n\'a pas pû être récupéré sur le serveur.';
	$lang['strimportfiletoobig'] = 'Erreur d\'importation : le fichier à charger est trop gros.';

	// Users
	$lang['strusername'] = 'Utilisateur';
	$lang['strpassword'] = 'Mot de passe';

	// Groups
	$lang['strgroup'] = 'Groupe';
	$lang['strgroupgroups'] = 'Groupes du groupe "%s"';
	$lang['strserversgroups'] = 'Groupes de serveurs';

	// Roles
	$lang['strrole'] = 'Rôle';
	$lang['strroles'] = 'Rôles';

	// Databases
	$lang['strdatabase'] = 'Base de données';
	$lang['strdatabases'] = 'Bases de données';
	$lang['strdatabaseslist'] = 'Databases du serveur';
	$lang['strnodatabases'] = 'Aucune base de données trouvée.';
	$lang['strsqlexecuted'] = 'Requête SQL exécutée.';

	// Schemas
	$lang['strschema'] = 'Schéma';
	$lang['strschemas'] = 'Schémas';
	$lang['strallschemas'] = 'Tous les schémas';
	$lang['strnoschemas'] = 'Aucun schéma trouvé.';

	// Tables
	$lang['strtable'] = 'Table';
	$lang['strtables'] = 'Tables';
	$lang['strtableslist'] = 'Tables du schéma "%s"';
	$lang['strnotables'] = 'Aucune table trouvée.';
	$lang['strestimatedrowcount'] = 'Nb lignes estimé';
	$lang['strtblproperties'] = 'Propriétés de la table "%s.%s"';
	$lang['strtblcontent'] = 'Contenu de la table "%s.%s"';
	$lang['emajemajlogtable'] = 'La table est une table de log E-Maj.';
	$lang['emajinternaltable'] = 'La table est une table interne E-Maj.';
	$lang['emajtblnogroupownership'] = 'La table ne fait actuellement partie d\'aucun groupe de tables.';

	// Sequences
	$lang['strsequence'] = 'Séquence';
	$lang['strsequences'] = 'Séquences';
	$lang['strsequenceslist'] = 'Séquences du schéma "%s"';
	$lang['strnosequences'] = 'Aucune séquence trouvée.';
	$lang['strseqproperties'] = 'Propriétés de la séquence "%s.%s"';
	$lang['strlastvalue'] = 'Dernière valeur';
	$lang['strincrement'] = 'Incrément';
	$lang['strstartvalue'] = 'Valeur de départ';
	$lang['strmaxvalue'] = 'Valeur maximale';
	$lang['strminvalue'] = 'Valeur minimale';
	$lang['strcachesize'] = 'Taille de cache';
	$lang['strlogcount'] = 'Comptage';
	$lang['strcancycle'] = 'Peut boucler?';
	$lang['striscalled'] = 'Incrémentera la dernière valeur avant de retourner la prochaine valeur (is_called) ?';
	$lang['emajemajlogsequence'] = 'La séquence est une séquence de log E-Maj.';
	$lang['emajinternalsequence'] = 'La séquence est une séquence interne E-Maj.';
	$lang['emajseqnogroupownership'] = 'La séquence ne fait actuellement partie d\'aucun groupe de tables.';

	// Constraints
	$lang['strconstraints'] = 'Contraintes';

	// Types
	$lang['strtype'] = 'Type';

	// Tablespaces
	$lang['strtablespace'] = 'Tablespace';

	// Triggers
	$lang['strtriggers'] = 'Triggers';
	$lang['strtrigger'] = 'Trigger';
	$lang['strnotriggerontable'] = 'La table n\'a pas de trigger.';
	$lang['emajapptriggers'] = 'Triggers applicatifs';
	$lang['emajapptriggershelp'] = 'Liste des triggers de la base de données, hors triggers système et E-Maj.';
	$lang['strnoapptrigger'] = 'Aucun trigger applicatif dans la base de données.';
	$lang['emajexecorder'] = 'Ordre Exec';
	$lang['emajtriggeringevent'] = 'Evénement déclencheur';
	$lang['emajcalledfunction'] = 'Fonction appelée';
	$lang['emajisemaj'] = 'E-Maj ?';
	$lang['emajisautodisable'] = 'Désactivation<br>auto';
	$lang['emajisautodisablehelp'] = 'Indique si le trigger est désactivé automatiquement lors des rollbacks E-Maj (défaut = ON = Oui)';
	$lang['emajswitchautodisable'] = 'Basculer désactivation auto';
	$lang['emajtriggerautook'] = 'Le trigger %s de la table %s.%s sera automatiquement désactivé lors des rollbacks E-Maj.';
	$lang['emajtriggernoautook'] = 'Le trigger %s de la table %s.%s ne sera PAS automatiquement désactivé lors des rollbacks E-Maj.';
	$lang['emajtriggerprocerr'] = 'Une erreur est survenue dans le traitement du trigger %s de la table %s.%s.';
	$lang['emajnoselectedtriggers'] = 'Aucun trigger sélectionné.';
	$lang['emajtriggersautook'] = '%s nouveaux triggers seront automatiquement désactivés lors des rollbacks E-Maj.';
	$lang['emajtriggersnoautook'] = '%s nouveaux triggers ne seront PAS automatiquement désactivés lors des rollbacks E-Maj.';
	$lang['emajorphantriggersexist'] = 'La table qui contient les identifiants de triggers à ne pas désactiver automatiquement lors des rollbacks E-Maj (emaj_ignored_app_trigger) référence des schémas, tables ou triggers qui n\'existent plus.';
	$lang['emajtriggersremovedok'] = '%s triggers ont été retirés.';

	// Miscellaneous
	$lang['strtopbar'] = 'Connexion : %s:%s - rôle « %s »';
	$lang['strlogintitle'] = 'Se connecter à %s';
	$lang['strlogoutmsg'] = 'Déconnecté de %s';
	$lang['strloading'] = 'Chargement...';
	$lang['strerrorloading'] = 'Erreur lors du chargement';
	$lang['strclicktoreload'] = 'Cliquer pour recharger';

//
// E-Maj strings
//
	// Basic strings
	$lang['emajnotavail'] = 'Désolé, E-Maj n\'est pas disponible ou accessible sur cette base de données. Plus de détails sur l\'onglet %s.';
	$lang['emajstate'] = 'Etat';
	$lang['emajnoselectedgroup'] = 'Aucun groupe de tables n\'a été sélectionné !';
	$lang['emajtablesgroup'] = 'Groupe de tables';
	$lang['emajgroup'] = 'Groupe';
	$lang['emajgroups'] = 'Groupes';
	$lang['emajmark'] = 'Marque';
	$lang['emajmarksetat'] = 'Posée à';
	$lang['emajgrouptype'] = 'Type de groupe';
	$lang['emajrollback'] = 'Rollback E-Maj';
	$lang['emajrollbacktype'] = 'Type de rollback';
	$lang['emajauditonly'] = 'Audit-seul';
	$lang['emajrollbackable'] = 'Rollbackable';
	$lang['emajunlogged'] = 'non tracé';
	$lang['emajlogged'] = 'tracé';
	$lang['emajlogging'] = 'Démarré';
	$lang['emajidle'] = 'Arrêté';
	$lang['emajactivemark'] = 'Marque active, donc utilisable pour un rollback E-Maj.';
	$lang['emajdeletedmark'] = 'Un arrêt de l\'enregistrement des mises à jour a rendu la marque inactive, donc inutilisable pour un rollback E-Maj.';
	$lang['emajprotectedmark'] = 'La protection mise sur la marque bloque les rollbacks E-Maj sur des marques antérieures.';
	$lang['emajprotected'] = 'Protégé contre les rollbacks E-Maj';
	$lang['emajpagebottom'] = 'Bas de la page';
	$lang['emajlogsize'] = 'Taille log';
	$lang['emajrequiredfield'] = 'Champ requis';
	$lang['emajestimates'] = 'Estimations';
	$lang['emajestimate'] = 'Estimer';
	$lang['emajreestimate'] = 'Ré-estimer';
	$lang['emajestimatedduration'] = 'Durée estimée';
	$lang['emajproperties'] = 'Propriétés E-Maj';
	$lang['emajschema'] = 'Schéma E-Maj';
	$lang['emajtrigger'] = 'Trigger E-Maj';
	$lang['emajselectfile'] = 'Sélectionner un fichier';
	$lang['emajnotjsonfile'] = 'Le fichier %s n\'a pas un format JSON valide.';
	$lang['emajtxid'] = 'Id. transaction';
	$lang['emajstartmark'] = 'Marque début';
	$lang['emajstartdatetime'] = 'Date-Heure début';
	$lang['emajendmark'] = 'Marque fin';
	$lang['emajenddatetime'] = 'Date-Heure fin';
	$lang['emajassign'] = 'Affecter';
	$lang['emajassigned'] = 'Affectée';
	$lang['emajmove'] = 'Déplacer';
	$lang['emajremove'] = 'Retirer';
	$lang['emajremoved'] = 'Retirée';

	// E-Maj html titles and tabs
	$lang['emajgroupsmanagement'] = 'Gestion des groupes E-Maj';
	$lang['emajgroupsconfiguration'] = 'Configuration des groupes de tables';
	$lang['emajgroupsconf'] = 'Configuration groupes';
	$lang['emajrollbacksmanagement'] = 'Gestion des rollbacks E-Maj';
	$lang['emajrlbkop'] = 'Rollbacks E-Maj';
	$lang['emajenvironment'] = 'Environnement E-Maj';
	$lang['emajenvir'] = 'E-Maj';
	$lang['emajlogstat'] = 'Statistiques log';

	// Common help messages
	$lang['emajmarknamehelp'] = 'Le nom de la marque doit être unique pour le groupe. Un caractère % représente l\'heure courante (format hh.mn.ss.ms).';
	$lang['emajmarknamemultihelp'] = 'Le nom de la marque doit être unique pour les groupes concernés. Un caractère % représente l\'heure courante (format hh.mn.ss.ms).';
	$lang['emajfiltershelp'] = 'Afficher/cacher les filtres. Les filtres sur le contenu des colonnes peuvent contenir des chaînes de caractères (abc), des nombres (123), des conditions d\'inégalité (>= 1000), des expressions rationnelles (/^ABC\d\d/), des conditions multiples avec les opérateurs \'and\', \'or\' ou \'!\'.';

	// E-Maj environment
	$lang['emajextnotavailable'] = 'Le logiciel E-Maj n\'est pas installé sur cette instance PostgreSQL.';
	$lang['emajextnotcreated'] = 'L\'extension emaj n\'est pas créée dans cette base de données.';
	$lang['emajcontactdba'] = 'Contactez votre administrateur des bases de données.';
	$lang['emajnogrant'] = 'Votre rôle de connexion n\'a pas les droits d\'utilisation d\'E-Maj. Utilisez un autre rôle ou contactez votre administrateur des bases de données.';
	$lang['emajcharacteristics'] = 'Caractéristiques de l\'environnement E-Maj';
	$lang['emajversions'] = 'Versions';
	$lang['emajpgversion'] = 'Version PostgreSQL : ';
	$lang['emajversion'] = 'Version E-Maj : ';
	$lang['emajasextension'] = 'installée comme extension';
	$lang['emajasscript'] = 'installée par script';
	$lang['emajtooold'] = 'Désolé, cette version d\'E-Maj (%s) est trop ancienne. La version minimum supportée par Emaj_web est %s.';
	$lang['emajversionmorerecent'] = 'Une version plus récente de l\'extension "emaj" existe, compatible avec cette version d\'Emaj_web.';
	$lang['emajwebversionmorerecent'] = 'Une version plus récente d\'Emaj_web existe probablement.';
	$lang['emajextensionmngt'] = 'Gestion de l\'extension "emaj"';
	$lang['emajcreateextension'] = 'Créer l\'extension';
	$lang['emajcreateemajextension'] = 'Créer l\'extension "emaj"';
	$lang['emajcreateextensionok'] = 'L\'extension "emaj" a été créée.';
	$lang['emajcreateextensionerr'] = 'Erreur lors de la création de l\'extension "emaj".';
	$lang['emajupdateextension'] = 'Mettre à jour l\'extension';
	$lang['emajupdateemajextension'] = 'Mettre à jour l\'extension "emaj"';
	$lang['emajupdateextensionok'] = 'L\'extension "emaj" a été mise à jour.';
	$lang['emajupdateextensionerr'] = 'Erreur lors de la mise à jour de l\'extension "emaj".';
	$lang['emajdropextension'] = 'Supprimer l\'extension';
	$lang['emajdropextensiongroupsexist'] = 'Pour supprimer l\'extension "emaj", supprimez les groupes de tables au préalable.';
	$lang['emajdropemajextension'] = 'Supprimer l\'extension "emaj"';
	$lang['emajconfirmdropextension'] = 'Confirmer la suppression de l\'extension "emaj"';
	$lang['emajdropextensionok'] = 'L\'extension "emaj" a été supprimée.';
	$lang['emajdropextensionerr'] = 'Erreur lors de la suppression de l\'extension "emaj".';
	$lang['emajdiskspace'] = 'Place disque occupée par l\'environnement E-Maj : %s de la base de données courante.';
	$lang['emajchecking'] = 'Intégrité de l\'environnement E-Maj';
	$lang['emajdiagnostics'] = 'Diagnostics';
	$lang['emajextparams'] = 'Paramètres de l\'extension';
	$lang['emajpargeneral'] = 'Paramètres généraux';
	$lang['emajparcostmodel'] = 'Paramètres du modèle de coûts des rollbacks E-Maj';
	$lang['emajparhistret'] = 'Délai de rétention des historiques';
	$lang['emajparhistretinfo'] = 'Le paramètre \'history_retention\' de la table emaj_param détermine le délai de rétention du contenu des différentes tables internes d\'historiques des opérations E-Maj. La valeur par défaut est de 1 an. Le paramètre est de type INTERVAL.';
	$lang['emajpardblinkcon'] = 'Chaine de connexion dblink';
	$lang['emajpardblinkconinfo'] = 'Le paramètre \'dblink_user_password\' de la table emaj_param définit la chaîne de connexion utilisée par dblink pour permettre le suivi des opérations de rollback E-Maj en cours d\'exécution. Le format du paramètre correspond aux chaînes de connexion habituelles pour PostgreSQL, par exemple \'user=<user> password=<password>\'. Par défaut le paramètre est vide, empếchant le suivi des opérations de rollback E-Maj.';
	$lang['emajparalterlog'] = 'Modification de la structure des tables de log';
	$lang['emajparalterloginfo'] = 'Le paramètre \'alter_log_table\' de la table emaj_param définit la modification de la structure des tables de log à leur création. Il prend la forme d\'une directive de type ALTER TABLE, par exemple \'ADD COLUMN emaj_appname TEXT DEFAULT current_setting(\'\'application_name\'\')\'. Le paramètre est vide par défaut.';
	$lang['emajparfixedstep'] = 'Coût fixe d\'une étape de rollback';
	$lang['emajparfixedstepinfo'] = 'Le paramètre \'fixed_step_rollback_duration\' de la table emaj_param détermine un coût fixe de traitement d\'une étape élémentaire de rollback E-Maj. Le paramètre est de type INTERVAL. La valeur par défaut est de 2,5 ms.';
	$lang['emajparfixeddblink'] = 'Surcoût dblink d\'une étape de rollback';
	$lang['emajparfixeddblinkinfo'] = 'Le paramètre \'fixed_dblink_rollback_duration\' de la table emaj_param détermine un surcoût pour chaque étape élémentaire de rollback E-Maj lorsqu\'une connexion dblink est utilisée. Le paramètre est de type INTERVAL. La valeur par défaut est de 4 ms.';
	$lang['emajparfixedrlbktbl'] = 'Coût fixe de rollback d\'une table';
	$lang['emajparfixedrlbktblinfo'] = 'Le paramètre \'fixed_table_rollback_duration\' de la table emaj_param détermine un coût fixe de rollback d\'une table ou séquence. Le paramètre est de type INTERVAL. La valeur par défaut est de 1 ms.';
	$lang['emajparavgrowrlbk'] = 'Coût moyen de rollback d\'une mise à jour';
	$lang['emajparavgrowrlbkinfo'] = 'Le paramètre \'avg_row_rollback_duration\' de la table emaj_param détermine le coût moyen de rollback d\'une mise à jour élémentaire. Le paramètre est de type INTERVAL. La valeur par défaut est de 100 µs.';
	$lang['emajparavgrowdel'] = 'Coût moyen de suppression d\'une mise à jour des logs';
	$lang['emajparavgrowdelinfo'] = 'Le paramètre \'avg_row_delete_log_duration\' de la table emaj_param détermine le coût moyen de suppression d\'une mise à jour élémentaire dans le log E-Maj. Le paramètre est de type INTERVAL. La valeur par défaut est de 10 µs.';
	$lang['emajparavgfkcheck'] = 'Coût moyen de vérification d\'une clé étrangère';
	$lang['emajparavgfkcheckinfo'] = 'Le paramètre \'avg_fkey_check_duration\' de la table emaj_param détermine le coût moyen de vérification d\'une clé étrangère. Le paramètre est de type INTERVAL. La valeur par défaut est de 20 µs.';

	// Import parameters
	$lang['emajimportparamconf'] = 'Importer une configuration de paramètres';
	$lang['emajdeletecurrentparam'] = 'Supprimer tous les paramètres existants';
	$lang['emajdeletecurrentparaminfo'] = 'Si la case est cochée, tous les paramètres présents dans l\'extension emaj sont supprimés avant le chargement du fichier.';
	$lang['emajcheckjsonparamconf101'] = 'La structure JSON ne contient pas de tableau "parameters".';
	$lang['emajcheckjsonparamconf102'] = 'Le paramètre #%s n\' pas d\'attribut  "key" ou a un attribut "key" à null.';
	$lang['emajcheckjsonparamconf103'] = 'Pour le paramètre "%s", l\'attribut "%s" est inconnu.';
	$lang['emajcheckjsonparamconf104'] = '"%s" n\'est pas un paramètre E-Maj connu.';
	$lang['emajcheckjsonparamconf105'] = 'La structure JSON référence plusieurs fois le paramètre "%s".';
	$lang['emajparamconfimported'] = '%s : %s paramètres importés depuis le fichier %s.';
	$lang['emajnewconf'] = 'Nouvelle configuration';
	$lang['emajnewmodifiedconf'] = 'Configuration modifiée';
	$lang['emajparamconfigimporterr'] = 'Erreur à l\'importation de paramètres à partir du fichier %s';

	// Dynamic groups content management
	$lang['emajlogdattsp'] = 'Tablespace table log';
	$lang['emajlogidxtsp'] = 'Tablespace index log';
	$lang['emajassigntable'] = 'E-Maj : Affecter des tables à un groupe de tables';
	$lang['emajthetable'] = 'la table "%s.%s"';
	$lang['emajconfirmassigntblseq'] = 'Affecter :';
	$lang['emajenterpriority'] = 'Priorité de traitement';
	$lang['emajpriorityhelp'] = 'Les tables sont traitées par ordre croissant de priorité, et par ordre alphabétique de nom si aucune priorité n\'est définie.';
	$lang['emajenterlogdattsp'] = 'Tablespace pour la table de log';
	$lang['emajenterlogidxtsp'] = 'Tablespace pour l\'index de la table de log';
	$lang['emajmarkiflogginggroup'] = 'Marque (si groupe démarré)';
	$lang['emajdynassigntablesok'] = '%s tables ont été assignées au groupe de tables %s.';
	$lang['emajmodifygrouperr'] = 'Erreur lors du changement de composition des groupes de tables.';
	$lang['emajmovetable'] = 'E-Maj : Déplacer des tables dans un autre groupe de tables';
	$lang['emajthetableingroup'] = 'la table "%s.%s" (groupe %s)';
	$lang['emajconfirmmovetblseq'] = 'Déplacer :';
	$lang['emajdynmovetablesok'] = '%s tables ont été déplacées dans le groupe de tables %s.';
	$lang['emajmodifytable'] = 'E-Maj : Modifier les propriétés E-Maj des tables';
//	$lang['emajconfirmmodifytblseq'] = 'Etes-vous sûr de vouloir modifier les propriétés de :';
	$lang['emajdynmodifytablesok'] = 'Les propriétés de %s tables ont été modifiées.';
	$lang['emajremovetable'] = 'E-Maj : Retirer des tables de leur groupe de tables';
	$lang['emajconfirmremovetblseq'] = 'Etes-vous sûr de vouloir retirer :';
	$lang['emajdynremovetablesok'] = '%s tables ont été retirées de leur groupe de tables.';
	$lang['emajassignsequence'] = 'E-Maj : Affecter des séquences à un groupe de tables';
	$lang['emajthesequence'] = 'la séquence "%s.%s"';
	$lang['emajdynassignsequencesok'] = '%s sequences ont été assignées au groupe de tables %s.';
	$lang['emajmovesequence'] = 'E-Maj : Déplacer des séquences dans un autre groupe de tables';
	$lang['emajthesequenceingroup'] = 'la séquence "%s.%s" (groupe %s)';
	$lang['emajdynmovesequencesok'] = '%s sequences ont été déplacées dans le groupe de tables %s.';
	$lang['emajremovesequence'] = 'E-Maj : Retirer des séquences de leur groupe de tables';
	$lang['emajdynremovesequencesok'] = '%s séquences ont été retirées de leur groupe de tables.';

	// Old Groups' content setup
	$lang['emajappschemas'] = 'Les schémas applicatifs';
	$lang['emajunknownobject'] = 'Cet objet est référencé dans la table emaj_group_def mais n\'est pas créé.';
	$lang['emajunsupportedobject'] = 'Ce type d\'objet n\'est pas supporté par E-Maj (unlogged table, table avec OIDS, table partitionnée,...).';
	$lang['emajtblseqofschema'] = 'Tables et séquences du schéma "%s"';
	$lang['emajlogschemasuffix'] = 'Suffixe schéma log';
	$lang['emajnamesprefix'] = 'Préfixe nom objets';
	$lang['emajspecifytblseqtoassign'] = 'Spécifiez au moins une table ou séquence à affecter';
	$lang['emajtblseqyetgroup'] = 'Erreur, "%s.%s" est déjà affecté à un groupe de tables.';
	$lang['emajtblseqbadtype'] = 'Erreur, le type de "%s.%s" n\'est pas supporté par E-Maj.';
	$lang['emajassigntblseq'] = 'E-Maj : Affecter des tables / séquences à un groupe de tables';
	$lang['emajfromgroup'] = 'du groupe "%s"';
	$lang['emajenterlogschema'] = 'Suffixe du schéma de log';
	$lang['emajlogschemahelp'] = 'Un schéma de log contient des tables, séquences et fonctions de log. Le schéma de log par défaut est \'emaj\'. Si un suffixe est défini pour la table, ses objets iront dans le schéma \'emaj\' + suffixe.';
	$lang['emajenternameprefix'] = 'Préfixe des noms d\'objets E-Maj';
	$lang['emajnameprefixhelp'] = 'Par défaut les noms des objets de log sont préfixés par &lt;schéma&gt;_&lt;table&gt;. Mais on peut définir un autre préfixe pour la table. Il doit être unique dans la base de données.';
	$lang['emajspecifytblseqtoupdate'] = 'Spécifiez au moins une table ou séquence à modifier';
	$lang['emajupdatetblseq'] = 'E-Maj : Modifier les propriétés d\'une table / séquence dans un groupe de tables';
	$lang['emajspecifytblseqtoremove'] = 'Spécifiez au moins une table ou séquence à retirer';
	$lang['emajtblseqnogroup'] = 'Erreur, "%s.%s" n\'est actuellement affecté à aucun groupe de tables.';
	$lang['emajremovetblseq'] = 'E-Maj : Retirer des tables / séquences de groupes de tables';
	$lang['emajconfirmremove1tblseq'] = 'Etes-vous sûr de vouloir retirer %s du groupe de tables "%s" ?';
	$lang['emajmodifygroupok'] = 'Le changement de configuration est enregistré. Il sera effectif après (re)création des groupes de tables concernés ou application des changements de configuration pour ces groupes.';
	$lang['emajspecifytblseqtoprocess'] = 'Spécifiez au moins une table ou séquence à traiter.';

	// List Groups
	$lang['emajidlegroups'] = 'Groupes de tables en état "arrêté" ';
	$lang['emajlogginggroups'] = 'Groupes de tables en état "démarré" ';
	$lang['emajconfiguredgroups'] = 'Groupes de tables "configurés" mais non encore "créés" ';
	$lang['emajlogginggrouphelp'] = 'Quand un groupe de tables est dans l\'état \'démarré\', les insertions, modifications et suppression de lignes sur ses tables sont enregistrées.';
 	$lang['emajidlegrouphelp'] = 'Quand un groupe de tables est dans l\'état \'arrêté\', les insertions, modifications et suppressions de lignes sur ses tables ne sont PAS enregistrées.';
	$lang['emajconfiguredgrouphelp'] = 'La configuration d\'un groupe définit les tables et séquences qui vont le constituer. Une fois \'configuré\', le groupe doit être \'créé\', afin de préparer tous les objets nécessaires à son utilisation (tables de log, fonctions,...).';
	$lang['emajcreationdatetime'] = 'Création';
	$lang['emajnbtbl'] = 'Tables';
	$lang['emajnbseq'] = 'Séquences';
	$lang['emajnbmark'] = 'Marques';
	$lang['emajsetmark'] = 'Poser une marque';
	$lang['emajsetcomment'] = 'Commenter';
	$lang['emajApplyConfChanges'] = 'Appliquer changements conf';
	$lang['emajnoidlegroup'] = 'Il n\'y a actuellement aucun groupe de tables en état "arrêté".';
	$lang['emajnologginggroup'] = 'Il n\'y a actuellement aucun groupe de tables en état "démarré".';
	$lang['emajnoconfiguredgroups'] = 'Il n\'y a actuellement aucun groupe de tables configuré mais non créé.';
	$lang['emajnoschema'] = 'Schéma inexistant (x%s) / ';
	$lang['emajinvalidschema'] = 'Schéma invalide (x%s) / ';
	$lang['emajnorelation'] = 'Table ou séquence inexistante (x%s) / ';
	$lang['emajinvalidtable'] = 'Type de table invalide (x%s) / ';
	$lang['emajduplicaterelation'] = 'Table ou séquence déjà affectée à un groupe (x%s) / ';
	$lang['emajnoconfiguredgroup'] = 'Pour créer un (autre) groupe de tables, allez d\'abord dans l\'onglet de configuration des groupes.<br>Vous pouvez aussi créer un groupe vide puis y ajouter des tables et séquences puis appliquer le changement de configuration.';
	$lang['emajcreateemptygroup'] = 'Créer un groupe vide';
	$lang['emajnewgroup'] = 'Nouveau groupe';

	// Rollback activity
	$lang['emajrlbkid'] = 'Id. Rlbk';
	$lang['emajrlbkstart'] = 'Début rollback';
	$lang['emajrlbkend'] = 'Fin rollback';
	$lang['emajduration'] = 'Durée';
	$lang['emajislogged'] = 'Tracé ?';
	$lang['emajnbsession'] = 'Sessions';
	$lang['emajnbproctable'] = 'Tables traitées';
	$lang['emajnbprocseq'] = 'Séquences traitées';
	$lang['emajcurrentduration'] = 'Durée actuelle';
	$lang['emajestimremaining'] = 'Restant estimée';
	$lang['emajpctcompleted'] = '% effectué';
	$lang['emajinprogressrlbk'] = 'Rollbacks E-Maj en cours';
	$lang['emajrlbkmonitornotavailable'] = 'Le suivi des rollbacks en cours n\'est pas disponible.';
	$lang['emajcompletedrlbk'] = 'Rollbacks E-Maj terminés';
	$lang['emajnbtabletoprocess'] = 'Tables à traiter';
	$lang['emajnbseqtoprocess'] = 'Séquences à traiter';
	$lang['emajnorlbk'] = 'Aucun rollback.';
	$lang['emajfilterrlbk1'] = 'Afficher les';
	$lang['emajfilterrlbk2'] = 'plus récents';
	$lang['emajfilterrlbk3'] = 'terminés depuis moins de';
	$lang['emajfilterrlbk4'] = 'heures';
	$lang['emajfilter'] = 'Filtrer';
	$lang['emajvisible'] = 'Visibles';
	$lang['emajconsolidablerlbk'] = 'Rollbacks E-Maj tracés consolidables';
	$lang['emajtargetmark'] = 'Marque cible';
	$lang['emajendrollbackmark'] = 'Marque fin de rollback';
	$lang['emajnbintermediatemark'] = 'Marques intermédiaires';
	$lang['emajconsolidate'] = 'Consolider';
	$lang['emajconsolidaterlbk'] = 'Consolider un rollback tracé';
	$lang['emajconfirmconsolidaterlbk'] = 'Etes-vous sûr de vouloir consolider le rollback terminé par la marque "%s" du groupe de tables "%s" ?';
	$lang['emajconsolidaterlbkok'] = 'Le rollback terminé par la marque "%s" du groupe de tables "%s" a été consolidé.';
	$lang['emajconsolidaterlbkerr'] = 'Erreur lors de la consolidation du rollback terminé par la marque "%s" du groupe de tables "%s" !';
	$lang['emajrlbkdetail'] = 'Détail du rollback E-Maj #%s';
	$lang['emajrlbkident'] = 'Identification du rollback';
	$lang['emajrlbkprogress'] = 'Progression du rollback';
	$lang['emajrlbkcharacteristics'] = 'Caractéristiques du rollback';
	$lang['emajrlbksessions'] = 'Sessions';
	$lang['emajrlbksession'] = 'Session';
	$lang['emajrlbkexecreport'] = 'Rapport d\'exécution';
	$lang['emajrlbkplanning'] = 'Planification';
	$lang['emajrlbkplanninghelp'] = 'Les étapes élémentaires d\'exécution du Rollback E-Maj. Ne sont pas inclus : la planification et la pose des verrous sur les tables en début d\'opération et le traitement des séquences en fin d\'opération.';
	$lang['emajrlbkestimmethodhelp'] = 'En phase de planification, la durée de chaque étape est estimée, en utilisant en priorité des statistiques d\'exécutions similaires passées, avec le même ordre de grandeur de quantités à traiter (STAT+), ou des ordres de grandeur différentes (STAT), ou, à défaut, les paramètres de l\'extension (PARAM).';
	$lang['emajhideestimates'] = 'Cacher estimations';
	$lang['emajshowestimates'] = 'Voir estimations';
	$lang['emajrlbkstep'] = 'Étape';
	$lang['emajestimatedquantity'] = 'Quantité estimée';
	$lang['emajestimationmethod'] = 'Méthode estimation';
	$lang['emajrlbkdisapptrg'] = 'Désactiver le trigger %s';
	$lang['emajrlbkdislogtrg'] = 'Désactiver le trigger de log';
	$lang['emajrlbkdropfk'] = 'Supprimer la clé étrangère %s';
	$lang['emajrlbksetfkdef'] = 'Positionner la clé étrangère %s DEFFERED';
	$lang['emajrlbkrlbktable'] = 'Exécuter le Rollback';
	$lang['emajrlbkdeletelog'] = 'Supprimer des log';
	$lang['emajrlbksetfkimm'] = 'Positionner la clé étrangère %s IMMEDIATE';
	$lang['emajrlbkaddfk'] = 'Recréer la clé étrangère %s';
	$lang['emajrlbkenaapptrg'] = 'Réactiver le trigger %s';
	$lang['emajrlbkenalogtrg'] = 'Réactiver le trigger de log';

	// Group's properties and marks
	$lang['emajgroupproperties'] = 'Propriétés du groupe de tables "%s"';
	$lang['emajcontent'] = 'Contenu';
	$lang['emajgroupmarks'] = 'Marques du groupe de tables "%s"';
	$lang['emajtimestamp'] = 'Date-Heure';
	$lang['emajnbchanges'] = 'Mises à jour';
	$lang['emajcumchanges'] = 'Cumul mises à jour';
	$lang['emajcumchangeshelp'] = 'Le cumul du nombre de mises à jour représente le nombre de mises à jour à annuler en cas de rollback E-Maj à la marque correspondante.';
	$lang['emajrlbk'] = 'Rollback';
	$lang['emajfirstmark'] = 'Première marque';
	$lang['emajrename'] = 'Renommer';
	$lang['emajnomark'] = 'Le groupe de tables n\'a pas de marque';
	$lang['emajprotect'] = 'Protéger';
	$lang['emajunprotect'] = 'Déprotéger';

	// Statistics
	$lang['emajshowstat'] = 'Statistiques issues du log E-Maj pour le groupe de tables "%s"';
	$lang['emajcurrentsituation'] = 'Situation courante';
	$lang['emajdetailedstat'] = 'Stats détaillées';
	$lang['emajdetailedlogstatwarning'] = 'Attention, le parcours des tables de log nécessaire à l\'obtention des statistiques détaillées peut être long';
	$lang['emajlogstatcurrentsituation'] = 'la situation courante';
	$lang['emajlogstatmark'] = 'la marque "%s"';
	$lang['emajlogstattittle'] = 'Mises à jour de table entre la marque "%s" et %s pour le groupe de tables "%s"';
	$lang['emajstatverb'] = 'Verbe SQL';
	$lang['emajnbinsert'] = 'INSERT';
	$lang['emajnbupdate'] = 'UPDATE';
	$lang['emajnbdelete'] = 'DELETE';
	$lang['emajnbtruncate'] = 'TRUNCATE';
	$lang['emajnbrole'] = 'Rôles';
	$lang['emajstatrows'] = 'Mises à jour';
	$lang['emajbrowsechanges'] = 'Voir les mises à jour';

	// Group's content
	$lang['emajgroupcontent'] = 'Contenu actuel du groupe de tables "%s"';
	$lang['emajemptygroup'] = 'Le groupe de tables "%s" est actuellement vide.';
	$lang['emajpriority'] = 'Priorité';
	$lang['emajlogtable'] = 'Table de log';

	// Group creation
	$lang['emajcreateagroup'] = 'E-Maj : Créer un groupe de tables';
	$lang['emajconfirmcreategroup'] = 'Etes-vous sûr de vouloir créer le groupe de tables "%s" ?';
	$lang['emajinvalidemptygroup'] = 'Erreur, le groupe de table "%s" est déjà créé ou configuré !';
	$lang['emajcreategroupok'] = 'Le groupe de tables "%s" a été créé.';
	$lang['emajcreategrouperr'] = 'Erreur lors de la création du groupe de tables "%s" !';

	// Groups content checks
	$lang['emajgroupconfok'] = 'La configuration du groupe de tables "%s" est correcte.';
	$lang['emajgroupconfwithdiag'] = 'Les contrôles sur la configuration du groupe de tables "%s" montrent que :';
	$lang['emajgroupsconfok'] = 'La configuration des groupes de tables "%s" est correcte.';
	$lang['emajgroupsconfwithdiag'] = 'Les contrôles sur la configuration des groupes de tables "%s" montrent que :';
	$lang['emajcheckconfgroups01'] = 'Dans le groupe "%s", la table ou séquence "%s.%s" n\'existe pas.';
	$lang['emajcheckconfgroups02'] = 'Dans le groupe "%s", la table "%s.%s" est une table partitionnée (seule les partitions élémentaires sont supportées par E-Maj).';
	$lang['emajcheckconfgroups03'] = 'Dans le groupe "%s", la table ou séquence "%s.%s" appartient à un schéma E-Maj.';
	$lang['emajcheckconfgroups04'] = 'Dans le groupe "%s", la table ou séquence "%s.%s" appartient déjà au groupe "%s".';
	$lang['emajcheckconfgroups05'] = 'Dans le groupe "%s", la table "%s.%s" est une table temporaire.';
	$lang['emajcheckconfgroups10'] = 'Dans le groupe "%s", la table "%s.%s" générerait un doublon de préfixe de noms E-Maj "%s".';
	$lang['emajcheckconfgroups11'] = 'Dans le groupe "%s", la table "%s.%s" a un préfixe de noms E-Maj déjà utilisé ("%s").';
	$lang['emajcheckconfgroups12'] = 'Dans le groupe "%s", pour la table "%s.%s", le tablespace de la table de log "%s" n\'existe pas.';
	$lang['emajcheckconfgroups13'] = 'Dans le groupe "%s", pour la table "%s.%s", le tablespace de l\'index de log "%s" n\'existe pas.';
	$lang['emajcheckconfgroups20'] = 'Dans le groupe "%s", la table "%s.%s" est une table UNLOGGED.';
	$lang['emajcheckconfgroups21'] = 'Dans le groupe "%s", la table "%s.%s" est déclarée WITH OIDS.';
	$lang['emajcheckconfgroups22'] = 'Dans le groupe "%s", la table "%s.%s" n\'a pas de PRIMARY KEY.';
	$lang['emajcheckconfgroups30'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le suffixe de schéma secondaire de log n\'est pas NULL.';
	$lang['emajcheckconfgroups31'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le préfixe des noms E-Maj n\'est pas NULL.';
	$lang['emajcheckconfgroups32'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le tablespace de la table de log n\'est pas NULL.';
	$lang['emajcheckconfgroups33'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le tablespace de l\'index de log n\'est pas NULL.';

	// Group drop
	$lang['emajdropagroup'] = 'E-Maj : Supprimer un groupe de tables';
	$lang['emajconfirmdropgroup'] = 'Etes-vous sûr de vouloir supprimer le groupe de tables "%s" ?';
	$lang['emajcantdropgroup'] = 'La suppression du groupe de tables "%s" est impossible. Le groupe est démarré.';
	$lang['emajdropgroupok'] = 'Le groupe de tables "%s" a été supprimé.';
	$lang['emajdropgrouperr'] = 'Erreur lors de la suppression du groupe de tables "%s" !';

	// Groups drop
	$lang['emajdropgroups'] = 'E-Maj : Supprimer les groupes de tables';
	$lang['emajconfirmdropgroups'] = 'Etes-vous sûr de vouloir supprimer les groupes de tables "%s" ?';
	$lang['emajcantdropgroups'] = 'La suppression des groupes de tables "%s" est impossible. Au moins un des groupes est démarré.';
	$lang['emajdropgroupsok'] = 'Les groupes de tables "%s" ont été supprimés.';
	$lang['emajdropgroupserr'] = 'Erreur lors de la suppression des groupes de tables "%s" !';

	// Export groups configuration
	$lang['emajexportgroupsconf'] = 'Exporter une configuration de groupes de tables';
	$lang['emajexportgroupsconfselect'] = 'Sélectionnez les groupes de tables dont la configuration sera exportée sur un fichier local.';

	// Import groups configuration
	$lang['emajimportgroupsconf'] = 'Importer une configuration de groupes de tables';
	$lang['emajimportgroupsinfile'] = 'Sélectionnez les groupes de tables à importer depuis le fichier "%s" :';
	$lang['emajimportgroupsinfileerr'] = 'Des erreurs ont été détectées dans le fichier "%s" :';
	$lang['emajcheckjsongroupsconf201'] = 'La structure JSON ne contient pas de tableau "tables_groups".';
 	$lang['emajcheckjsongroupsconf202'] = 'La structure JSON référence plusieurs fois le groupe de tables "%s".';
	$lang['emajcheckjsongroupsconf210'] = 'Le groupe de tables #%s ne contient pas d\'attribut "group".';
	$lang['emajcheckjsongroupsconf211'] = 'Pour le groupe de tables "%s", le mot clé "%s" est inconnu.';
	$lang['emajcheckjsongroupsconf212'] = 'Pour le groupe de tables "%s", l\'attribut "is_rollbackable" n\'est pas un booléen.';
	$lang['emajcheckjsongroupsconf220'] = 'Dans le groupe de tables "%s", la table #%s n\'a pas d\'attribut "schema".';
	$lang['emajcheckjsongroupsconf221'] = 'Dans le groupe de tables "%s", la table #%s n\'a pas d\'attribut "table".';
	$lang['emajcheckjsongroupsconf222'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, the mot clé "%s" est inconnu.';
	$lang['emajcheckjsongroupsconf223'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, l\'attribut "priority" n\'est pas un nombre.';
	$lang['emajcheckjsongroupsconf224'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger #%s n\'a pas d\'attribut "trigger".';
	$lang['emajcheckjsongroupsconf225'] = 'Dans le groupe de tables "%s" et pour un trigger de la table %s.%s, le mot clé "%s" est inconnu.';
	$lang['emajcheckjsongroupsconf230'] = 'Dans le groupe de tables "%s", la sequence #%s n\'a pas d\'attribut "schema".';
	$lang['emajcheckjsongroupsconf231'] = 'Dans le groupe de tables "%s", la sequence #%s n\'a pas d\'attribut "sequence".';
	$lang['emajcheckjsongroupsconf232'] = 'Dans le groupe de tables "%s" et pour la sequence %s.%s, le mot clé "%s" est inconnnu.';
	$lang['emajgroupsconfimport250'] = 'Le groupe de tables "%s" à importer n\'est pas référencé dans la structure JSON.';
	$lang['emajgroupsconfimport251'] = 'Le groupe de tables "%s" existe déjà.';
	$lang['emajgroupsconfimport252'] = 'Changer le type du groupe de tables "%s" n\est pas permis.';
	$lang['emajgroupsconfimport260'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger %s n\'existe pas.';
	$lang['emajgroupsconfimport261'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger %s est un trigger E-Maj.';
	$lang['emajgroupsconfimportpreperr'] = 'L\'importation de la configuration des groupes de tables "%s" depuis le fichier "%s" a échoué pour les raisons suivantes :';
	$lang['emajgroupsconfimported'] = '%s groupes de tables ont été importés depuis le fichier "%s".';
	$lang['emajgroupsconfimporterr'] = 'Erreur à l\'importation de groupes de tables à partir du fichier "%s"';

	// Group alter
	$lang['emajalteraloggingroup'] = 'Le groupe "%s" est actif. Vous pouvez spécifier un nom de marque.';
	$lang['emajconfirmaltergroup'] = 'Etes-vous sûr de vouloir appliquer les changements de configuration pour le groupe de tables "%s" ?';
	$lang['emajcantaltergroup'] = 'La modification du groupe "%s" générerait des actions qui ne peuvent être effectuées sur un groupe actif. Arrêtez le groupe avant de le modifier.';
	$lang['emajaltergroupok'] = 'Les changements de configuration du groupe de tables "%s" ont été appliqués.';
	$lang['emajaltergrouperr'] = 'Erreur lors de l\'application des changements de configuration pour le groupe de tables "%s" !';

	// Groups alter
	$lang['emajaltergroups'] = 'E-Maj : Appliquer les changements de configuration';
	$lang['emajalterallloggingroups'] = 'Les groupes "%s" sont actifs. Vous pouvez spécifier un nom de marque.';
	$lang['emajconfirmaltergroups'] = 'Etes-vous sûr de vouloir appliquer les changements de configuration pour les groupes de tables "%s" ?';
	$lang['emajaltergroupsok'] = 'Les changements de configuration des groupes de tables "%s" ont été appliqués.';
	$lang['emajaltergroupserr'] = 'Erreur lors de l\'application des changements de configuration pour les groupes de tables "%s" !';

	// Group comment
	$lang['emajcommentagroup'] = 'E-Maj : Enregistrer un commentaire pour un groupe de tables ';
	$lang['emajcommentgroup'] = 'Entrer, modifier ou supprimer un commentaire pour le groupe de tables "%s"';
	$lang['emajcommentgroupok'] = 'Le commentaire a été enregistré pour le groupe de tables "%s".';
	$lang['emajcommentgrouperr'] = 'Erreur lors de l\'enregistrement du commentaire pour le groupe de tables "%s" !';

	// Group protect
	$lang['emajcantprotectgroup'] = 'La protection du groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$lang['emajprotectgroupok'] = 'Le groupe de tables "%s" est maintenant protégé contre les rollbacks.';
	$lang['emajprotectgrouperr'] = 'Erreur lors de la protection du groupe de tables "%s" !';

	// Group unprotect
	$lang['emajcantunprotectgroup'] = 'La déprotection du groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$lang['emajunprotectgroupok'] = 'Le groupe de tables "%s" est maintenant déprotégé.';
	$lang['emajunprotectgrouperr'] = 'Erreur lors de la deprotection du groupe de tables "%s" !';

	// Group start
	$lang['emajstartagroup'] = 'E-Maj : Démarrer un groupe de tables';
	$lang['emajconfirmstartgroup'] = 'Démarrage du groupe de tables "%s"';
	$lang['emajinitmark'] = 'Marque initiale';
	$lang['emajoldlogsdeletion'] = 'Suppression des anciens logs';
	$lang['emajcantstartgroup'] = 'Le démarrage du groupe de tables "%s" est impossible. Le groupe est déjà démarré.';
	$lang['emajstartgroupok'] = 'Le groupe de tables "%s" est démarré avec la marque "%s".';
	$lang['emajstartgrouperr'] = 'Erreur lors du démarrage du groupe de tables "%s" !';

	// Groups start
	$lang['emajstartgroups'] = 'E-Maj : Démarrer des groupes de tables';
	$lang['emajconfirmstartgroups'] = 'Démarrage des groupes de tables "%s"';
	$lang['emajcantstartgroups'] = 'Le démarrage des groupes de tables "%s" est impossible. Le groupe "%s" est déjà démarré.';
	$lang['emajstartgroupsok'] = 'Les groupes de tables "%s" ont été démarrés avec la marque "%s".';
	$lang['emajstartgroupserr'] = 'Erreur lors du démarrage des groupes de tables "%s" !';

	// Group stop
	$lang['emajstopagroup'] = 'E-Maj : Arrêter un groupe de tables ';
	$lang['emajconfirmstopgroup'] = 'Arrêt du groupe de tables "%s"';
	$lang['emajstopmark'] = 'Marque finale';
	$lang['emajforcestop'] = 'Forcer l\'arrêt (en cas de problème seulement)';
	$lang['emajcantstopgroup'] = 'L\'arrêt du groupe de tables "%s" est impossible. Le groupe est déjà arrêté.';
	$lang['emajstopgroupok'] = 'Le groupe de tables "%s" a été arrêté.';
	$lang['emajstopgrouperr'] = 'Erreur lors de l\'arrêt du groupe de tables "%s" !';

	// Groups stop
	$lang['emajstopgroups'] = 'E-Maj : Arrêter des groupes de tables';
	$lang['emajconfirmstopgroups'] = 'Arrêt des groupes de tables "%s"';
	$lang['emajcantstopgroups'] = 'L\'arrêt des groupes de tables "%s" est impossible. Le groupe "%s" est déjà arrêté.';
	$lang['emajstopgroupsok'] = 'Les groupes de tables "%s" ont été arrêtés.';
	$lang['emajstopgroupserr'] = 'Erreur lors de l\'arrêt des groupes de tables "%s" !';

	// Group reset
	$lang['emajresetagroup'] = 'E-Maj : Réinitialiser un groupe de tables';
	$lang['emajconfirmresetgroup'] = 'Etes-vous sûr de vouloir réinitialiser le groupe de tables "%s" ?';
	$lang['emajcantresetgroup'] = 'La réinitialisation du groupe de tables "%s" est impossible. Le groupe est démarré.';
	$lang['emajresetgroupok'] = 'Le groupe de tables "%s" est réinitialisé.';
	$lang['emajresetgrouperr'] = 'Erreur lors de la réinitialisation du groupe de tables "%s" !';

	// Groups reset
	$lang['emajresetgroups'] = 'E-Maj : Réinitialiser des groupes de tables';
	$lang['emajconfirmresetgroups'] = 'Etes-vous sûr de vouloir réinitialiser les groupe de tables "%s" ?';
	$lang['emajcantresetgroups'] = 'La réinitialisation des groupes de tables "%s" est impossible. Au moins un groupe est démarré.';
	$lang['emajresetgroupsok'] = 'Les groupes de tables "%s" ont été réinitialisés.';
	$lang['emajresetgroupserr'] = 'Erreur lors de la réinitialisation des groupes de tables "%s" !';

	// Set Mark for one or several groups
	$lang['emajsetamark'] = 'E-Maj : Poser une marque';
	$lang['emajconfirmsetmarkgroup'] = 'Pose d\'une marque pour le groupe de tables "%s" :';
	$lang['emajcantsetmarkgroup'] = 'La pose d\'une marque pour le groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$lang['emajinvalidmark'] = 'La marque saisie (%s) est invalide.';
	$lang['emajsetmarkgroupok'] = 'La marque "%s" est posée pour le groupe de tables "%s".';
	$lang['emajsetmarkgrouperr'] = 'Erreur lors de la pose de la marque "%s" pour le groupe de tables "%s" !';
	$lang['emajconfirmsetmarkgroups'] = 'Pose d\'une marque pour les groupes de tables "%s" :';
	$lang['emajcantsetmarkgroups'] = 'La pose d\'une marque pour le groupe de tables "%s" est impossible. Le groupe "%s" est arrêté.';
	$lang['emajsetmarkgroupsok'] = 'La marque "%s" est posée pour les groupes de tables "%s".';
	$lang['emajsetmarkgroupserr'] = 'Erreur lors de la pose de la marque "%s" pour les groupes de tables "%s" !';

	// Protect mark
	$lang['emajcantprotectmarkgroup'] = 'La protection d\'une marque pour le groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$lang['emajprotectmarkgroupok'] = 'La marque "%s" du groupe de tables "%s" est maintenant protégé contre les rollbacks.';
	$lang['emajprotectmarkgrouperr'] = 'Erreur lors de la protection de la marque "%s" du groupe de tables "%s" !';

	// Unprotect mark
	$lang['emajcantunprotectmarkgroup'] = 'La déprotection d\'une marque pour le groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$lang['emajunprotectmarkgroupok'] = 'La marque "%s" du groupe de tables "%s" est maintenant déprotégé.';
	$lang['emajunprotectmarkgrouperr'] = 'Erreur lors de la déprotection de la marque "%s" du groupe de tables "%s" !';

	// Comment mark
	$lang['emajcommentamark'] = 'E-Maj : Enregistrer un commentaire pour une marque';
	$lang['emajcommentmark'] = 'Entrer, modifier ou supprimer le commentaire pour la marque "%s" du groupe de tables "%s".';
	$lang['emajcommentmarkok'] = 'Le commentaire a été enregistré pour la marque "%s" du groupe de tables "%s".';
	$lang['emajcommentmarkerr'] = 'Erreur lors de l\'enregistrement du commentaire pour la marque "%s" du groupe de tables "%s" !';

	// Group rollback
	$lang['emajrlbkagroup'] = 'E-Maj : Rollbacker un groupe de tables';
	$lang['emajconfirmrlbkgroup'] = 'Rollback du groupe de tables "%s" à la marque "%s"';
	$lang['emajselectmarkgroup'] = 'Rollback du groupe de tables "%s" à la marque : ';
	$lang['emajunknownestimate'] = 'non connue';
	$lang['emajdurationminutesseconds'] = '%s min %s s';
	$lang['emajdurationhoursminutes'] = '%s h %s min';
	$lang['emajdurationovertendays'] = '> 10 jours';
	$lang['emajrlbkthenmonitor'] = 'Rollback et suivi';
	$lang['emajcantrlbkidlegroup'] = 'Le rollback du groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$lang['emajcantrlbkprotgroup'] = 'Le rollback du groupe de tables "%s" est impossible. Le groupe est protégé.';
	$lang['emajcantrlbkinvalidmarkgroup'] = 'Le rollback du groupe de tables "%s" est impossible. La marque "%s" n\'est pas valide.';
	$lang['emajreachaltergroup'] = 'Le rollback du groupe de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification du groupe. Veuillez confirmer le rollback.';
	$lang['emajautorolledback'] = 'Annulé automatiquement ?';
	$lang['emajrlbkgroupok'] = 'Le rollback du groupe de tables "%s" à la marque "%s" est effectué.';
	$lang['emajrlbkgrouperr'] = 'Erreur lors du rollback du groupe de tables "%s" à la marque "%s" !';
	$lang['emajbadconfparam'] = 'Erreur : le rollback asynchrone n\'est plus possible. Vérifiez l\'existence de l\'extension dblink et la valeur des deux paramètres de configuration du chemin de la commande psql (%s) et du répertoire temporaire (%s).';
	$lang['emajasyncrlbkstarted'] = 'Rollback #%s démarré.';
	$lang['emajrlbkgroupreport'] = 'Rapport d\'exécution du rollback du groupe de tables "%s" à la marque "%s"';

	// Groups rollback
	$lang['emajrlbkgroups'] = 'E-Maj : Rollbacker des groupes de tables';
	$lang['emajselectmarkgroups'] = 'Rollback des groupes de tables "%s" à la marque : ';
	$lang['emajcantrlbkidlegroups'] = 'Le rollback des groupes de tables "%s" est impossible. Le groupe "%s" est arrêté.';
	$lang['emajcantrlbkprotgroups'] = 'Le rollback des groupes de tables "%s" est impossible. Les groupes "%s" sont protégés.';
	$lang['emajnomarkgroups'] = 'Aucune marque commune aux groupes de tables "%s" ne peut être utilisée pour un rollback.';
	$lang['emajcantrlbkinvalidmarkgroups'] = 'Le rollback des groupes de tables "%s" est impossible. La marque "%s" n\'est pas valide.';
	$lang['emajreachaltergroups'] = 'Le rollback des groupes de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification de groupes. Veuillez confirmer le rollback.';
	$lang['emajrlbkgroupsok'] = 'Le rollback des groupes de tables "%s" à la marque "%s" est effectué.';
	$lang['emajrlbkgroupserr'] = 'Erreur lors du rollback des groupes de tables "%s" à la marque "%s" !';
	$lang['emajrlbkgroupsreport'] = 'Rapport d\'exécution du rollback des groupes de tables "%s" à la marque "%s"';

	// Elementary alter group actions previously executed, reported at rollback time 
	$lang['emajalteredremovetbl'] = 'La table "%s.%s" a été supprimée du groupe de tables %s';
	$lang['emajalteredremoveseq'] = 'La séquence "%s.%s" a été supprimée du groupe de tables %s';
	$lang['emajalteredrepairtbl'] = 'Les objets E-Maj pour la table "%s.%s" ont été reparés';
	$lang['emajalteredrepairseq'] = 'Les objets E-Maj pour la séquence "%s.%s" ont été reparés';
	$lang['emajalteredchangetbllogschema'] = 'Le schéma de log E-Maj pour la table "%s.%s" a été modifié';
	$lang['emajalteredchangetblnamesprefix'] = 'Le préfixe des noms E-Maj pour la table "%s.%s" a été modifié';
	$lang['emajalteredchangetbllogdatatsp'] = 'Le tablespace pour le log de la table "%s.%s" a été modifié';
	$lang['emajalteredchangetbllogindextsp'] = 'Le tablespace pour les index de log de la table "%s.%s" a été modifié';
	$lang['emajalteredassignrel'] = 'La table ou séquence "%s.%s" a été déplacée du groupe de tables "%s" au groupe de tables "%s"';
	$lang['emajalteredchangerelpriority'] = 'La priorité E-Maj pour la table "%s.%s" a été modifiée';
	$lang['emajalteredaddtbl'] = 'La table "%s.%s" a été ajoutée au groupe de tables "%s"';
	$lang['emajalteredaddseq'] = 'La séquence "%s.%s" a été ajoutée au groupe de tables "%s"';

	// Mark renaming
	$lang['emajrenameamark'] = 'E-Maj : Renommer une marque';
	$lang['emajconfirmrenamemark'] = 'Renomage de la marque "%s" du groupe de tables "%s"';
	$lang['emajnewnamemark'] = 'Nouveau nom';
	$lang['emajrenamemarkok'] = 'La marque "%s" du groupe de tables "%s" a été renommée en "%s".';
	$lang['emajrenamemarkerr'] = 'Erreur lors du renommage de la marque "%s" du groupe de tables "%s" en "%s" !';

	// Mark deletion
	$lang['emajdelamark'] = 'E-Maj : Effacer une marque';
	$lang['emajconfirmdelmark'] = 'Etes-vous sûr de vouloir effacer la marque "%s" pour le groupe de tables "%s" ?';
	$lang['emajdelmarkok'] = 'La marque "%s" a été effacée pour le groupe de tables "%s".';
	$lang['emajdelmarkerr'] = 'Erreur lors de l\'effacement de la marque "%s" pour le groupe de tables "%s" !';

	// Marks deletion
	$lang['emajdelmarks'] = 'E-Maj : Effacer des marques';
	$lang['emajconfirmdelmarks'] = 'Etes-vous sûr de vouloir effacer les marques "%s" pour le groupe de tables "%s" ?';
	$lang['emajdelmarksok'] = 'Les marques "%s" ont été effacées pour le groupe de tables "%s".';
	$lang['emajdelmarkserr'] = 'Erreur lors de l\'effacement des marques "%s" pour le groupe de tables "%s" !';

	// Marks before mark deletion
	$lang['emajdelmarksprior'] = 'E-Maj : Supprimer des marques';
	$lang['emajconfirmdelmarksprior'] = 'Etes-vous sûr de vouloir supprimer toutes les marques et log antérieurs à la marque "%s" pour le groupe de tables "%s" ?';
	$lang['emajdelmarkspriorok'] = 'Les (%s) marques antérieures à la marque "%s" ont été supprimées pour le groupe de tables "%s".';
	$lang['emajdelmarkspriorerr'] = 'Erreur lors de la suppression des marques antérieures à la marque "%s" pour le groupe de tables "%s" !';

?>
