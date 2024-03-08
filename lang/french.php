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
	$lang['stroldtimestampformat'] = 'dd MMM YYYY HH:mm:ss';
	// - the recent times, producing something like 'Mon 23 Jun 12:34:56'
	$lang['strrecenttimestampformat'] = 'EEE dd MMM HH:mm:ss';
	// - the timestamp abbreviated into time with milliseconds
	$lang['strprecisetimeformat'] = 'HH:mm:ss.SSS';
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
	$lang['strrecreate'] = 'Recréer';
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
	$lang['strrows'] = 'ligne(s)';
	$lang['strrowsaff'] = 'ligne(s) affectée(s).';
	$lang['strback'] = 'Retour';
	$lang['strqueryresults'] = 'Résultats de la requête';
	$lang['strencoding'] = 'Codage';
	$lang['strsql'] = 'SQL';
	$lang['strexecute'] = 'Exécuter';
	$lang['strconfirm'] = 'Confirmer';
	$lang['strellipsis'] = '...';
	$lang['strexpand'] = 'Étendre';
	$lang['strcollapse'] = 'Réduire';
	$lang['strrefresh'] = 'Rafraîchir';
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
	$lang['strautorefresh'] = 'Rafraîchissement auto';
	$lang['strbacktolist'] = 'Retour à la liste';
	$lang['stredit'] = 'Éditer';
	$lang['strclear'] = 'Effacer';

	// User-supplied SQL editing
	$lang['strsqledit'] = 'Édition de requête SQL';
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
	$lang['strpostgresqlversionnotsupported'] = 'Cette version de PostgreSQL n\'est pas supportée. La version minimum supportée est la %s.';
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
	$lang['strpk'] = 'Clé primaire';

	// Types
	$lang['strtype'] = 'Type';

	// Tablespaces
	$lang['strtablespace'] = 'Tablespace';

	// Triggers
	$lang['strtrigger'] = 'Trigger';
	$lang['strtriggers'] = 'Triggers';
	$lang['strnotriggerontable'] = 'La table n\'a pas de trigger.';
	$lang['emajapptriggers'] = 'Triggers applicatifs';
	$lang['emajapptriggershelp'] = 'Liste des triggers de la base de données, hors triggers système et E-Maj.';
	$lang['strnoapptrigger'] = 'Aucun trigger applicatif dans la base de données.';
	$lang['emajexecorder'] = 'Ordre Exec';
	$lang['emajtriggeringevent'] = 'Événement déclencheur';
	$lang['emajcalledfunction'] = 'Fonction appelée';
	$lang['emajisemaj'] = 'E-Maj ?';
	$lang['emajisautodisable'] = 'Désactivation<br>auto';
	$lang['emajisautodisablehelp'] = 'Indique si le trigger est désactivé automatiquement lors des rollbacks E-Maj (défaut = ON = Oui)';
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
	$lang['emajstate'] = 'État';
	$lang['emajnoselectedgroup'] = 'Aucun groupe de tables n\'a été sélectionné !';
	$lang['emajtablesgroup'] = 'Groupe de tables';
	$lang['emajgroup'] = 'Groupe';
	$lang['emajgroups'] = 'Groupes';
	$lang['emajnewgroup'] = 'Nouveau groupe';
	$lang['emajmark'] = 'Marque';
	$lang['emajgroupcreatedat'] = 'Créé le';
	$lang['emajgroupcreateddroppedat'] = 'Créé/supprimé le';
	$lang['emajgrouplatesttype'] = 'Dernier type';
	$lang['emajgrouplatestdropat'] = 'Dernière suppression à';
	$lang['emajlogsession'] = 'Session de log';
	$lang['emajgroupstartedat'] = 'Démarré le';
	$lang['emajgroupstoppedat'] = 'Arrêté le';
	$lang['emajmarksetat'] = 'Posée le';
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
	$lang['emajvisible'] = 'Visibles';
	$lang['emajsetmark'] = 'Poser une marque';
	$lang['emajsetcomment'] = 'Commenter';
	$lang['emajforget'] = 'Oublier';

	// E-Maj html titles and tabs
	$lang['emajgroupsmanagement'] = 'Gestion des groupes E-Maj';
	$lang['emajgroupsconfiguration'] = 'Configuration des groupes de tables';
	$lang['emajgroupsconf'] = 'Conf.groupes';
	$lang['emajrollbacksmanagement'] = 'Gestion des rollbacks E-Maj';
	$lang['emajrlbkop'] = 'Rollbacks E-Maj';
	$lang['emajenvironment'] = 'Environnement E-Maj';
	$lang['emajenvir'] = 'E-Maj';
	$lang['emajchangesstat'] = 'Statistiques / Mises à jour';
	$lang['emajhistory'] = 'Historique';

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
	$lang['emajwarningdevel'] = 'Accéder à une extension emaj en version <devel> peut poser des problèmes. Il est conseillé d\'obtenir une version stable d\'emaj depuis pgxn.org.';
	$lang['emajextensionmngt'] = 'Gestion de l\'extension "emaj"';
	$lang['emajcreateextension'] = 'Créer l\'extension';
	$lang['emajcreateemajextension'] = 'Créer l\'extension "emaj"';
	$lang['emajnocompatibleemajversion'] = 'Aucune version d\'extension emaj installée n\'est compatible avec la version de PostgreSQL.';
	$lang['emajcreateextensionok'] = 'L\'extension "emaj" a été créée.';
	$lang['emajcreateextensionerr'] = 'Erreur lors de la création de l\'extension "emaj".';
	$lang['emajupdateextension'] = 'Mettre à jour l\'extension';
	$lang['emajupdateemajextension'] = 'Mettre à jour l\'extension "emaj"';
	$lang['emajmissingeventtriggers'] = 'Il manque des triggers sur événement. Cela bloque les mises à jour de version >= 4.2.0. Exécutez le script sql/emaj_upgrade_after_postgres_upgrade.sql ou supprimez et réinstallez l\'extension.';
	$lang['emajnocompatibleemajupdate'] = 'Aucune mise à jour d\'extension emaj installée n\'est compatible avec la version de PostgreSQL.';
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

	// Dynamic groups content management: common messages
	$lang['emajlogdattsp'] = 'Tablespace table log';
	$lang['emajlogidxtsp'] = 'Tablespace index log';
	$lang['emajthetable'] = 'la table "%s.%s"';
	$lang['emajthesequence'] = 'la séquence "%s.%s"';
	$lang['emajthetblseqingroup'] = '%s (groupe %s)';
	$lang['emajenterpriority'] = 'Priorité de traitement';
	$lang['emajpriorityhelp'] = 'Les tables sont traitées par ordre croissant de priorité, et par ordre alphabétique de nom si aucune priorité n\'est définie.';
	$lang['emajenterlogdattsp'] = 'Tablespace pour la table de log';
	$lang['emajenterlogidxtsp'] = 'Tablespace pour l\'index de la table de log';
	$lang['emajmarkiflogginggroup'] = 'Marque (si groupe démarré)';

	// Dynamic groups content management: generic error messages
	$lang['emajschemamissing'] = 'Le schéma "%s" n\'existe plus.';
	$lang['emajtablemissing'] = 'La table "%s.%s" n\'existe plus.';
	$lang['emajsequencemissing'] = 'La séquence "%s.%s" n\'existe plus';
	$lang['emajtablesmissing'] = '%s tables (%s) n\'existent plus.';
	$lang['emajsequencesmissing'] = '%s séquences (%s) n\'existent plus.';

	// Assign tables
	$lang['emajassigntable'] = 'E-Maj : Affecter des tables à un groupe de tables';
	$lang['emajconfirmassigntable'] = 'Affecter la table "%s.%s"';
	$lang['emajconfirmassigntables'] = 'Affecter ces %s tables du schéma "%s" :';
	$lang['emajassigntableok'] = '%s table a été affectée au groupe de tables %s.';
	$lang['emajassigntablesok'] = '%s tables ont été affectées au groupe de tables %s.';
	$lang['emajassigntableerr'] = 'Erreur lors de l\'affectation de la table "%s.%s".';
	$lang['emajassigntableerr2'] = 'Erreur lors de l\'affectation de la table "%s.%s" dans le groupe de tables "%s".';
	$lang['emajassigntableserr'] = 'Erreur lors de l\'affectation des %s tables du schéma "%s".';
	$lang['emajassigntableserr2'] = 'Erreur lors de l\'affectation des %s tables du schéma "%s" dans le groupe de tables "%s".';

	// Move tables
	$lang['emajmovetable'] = 'E-Maj : Déplacer des tables dans un autre groupe de tables';
	$lang['emajconfirmmovetable'] = 'Déplacer la table "%s.%s" de son groupe de tables "%s".';
	$lang['emajconfirmmovetables'] = 'Déplacer ces %s tables du schéma "%s" :';
	$lang['emajmovetableok'] = '%s table a été déplacée dans le groupe de tables %s.';
	$lang['emajmovetablesok'] = '%s tables ont été déplacées dans le groupe de tables %s.';
	$lang['emajmovetableerr'] = 'Erreur lors du déplacement de la table "%s.%s".';
	$lang['emajmovetableerr2'] = 'Erreur lors du déplacement de la table "%s.%s" du groupe de tables "%s" vers le groupe de tables "%s".';
	$lang['emajmovetableserr'] = 'Erreur lors du déplacement des %s tables du schéma "%s".';
	$lang['emajmovetableserr2'] = 'Erreur lors du déplacement des %s tables du schéma "%s" de leur groupe de tables vers le groupe de tables "%s".';

	// Modify table
	$lang['emajmodifytable'] = 'E-Maj : Modifier les propriétés E-Maj de tables';
	$lang['emajconfirmmodifytable'] = 'Modifier les propriétés de la table "%s.%s".';
	$lang['emajmodifytablesok'] = 'Les propriétés E-Maj de %s tables ont été modifiées.';
	$lang['emajmodifytableerr'] = 'Erreur lors de la modification des propiétés E-Maj de la table "%s.%s".';

	// Remove tables
	$lang['emajremovetable'] = 'E-Maj : Retirer des tables de leur groupe de tables';
	$lang['emajconfirmremovetable'] = 'Retirer la table "%s.%s" de son groupe de tables "%s".';
	$lang['emajconfirmremovetables'] = 'Retirer ces %s tables du schéma "%s" de leur groupe de tables :';
	$lang['emajremovetableok'] = '%s table a été retirée de son groupe de tables.';
	$lang['emajremovetablesok'] = '%s tables ont été retirées de leur groupe de tables.';
	$lang['emajremovetableerr'] = 'Erreur lors de la sortie de la table "%s.%s" du groupe de tables "%s".';
	$lang['emajremovetableserr'] = 'Erreur lors de la sortie des %s tables du schéma "%s" de leur groupe de tables.';

	// Assign sequences
	$lang['emajassignsequence'] = 'E-Maj : Affecter des séquences à un groupe de tables';
	$lang['emajconfirmassignsequence'] = 'Affecter la séquence "%s.%s"';
	$lang['emajconfirmassignsequences'] = 'Affecter ces %s séquences du schéma "%s" :';
	$lang['emajassignsequenceok'] = '%s sequence a été affectée au groupe de tables %s.';
	$lang['emajassignsequencesok'] = '%s sequences ont été affectées au groupe de tables %s.';
	$lang['emajassignsequenceerr'] = 'Erreur lors de l\'affectation de la séquence "%s.%s".';
	$lang['emajassignsequenceerr2'] = 'Erreur lors de l\'affectation de la séquence "%s.%s" dans le groupe de tables "%s".';
	$lang['emajassignsequenceserr'] = 'Erreur lors de l\'affectation des %s séquences du schéma "%s".';
	$lang['emajassignsequenceserr2'] = 'Erreur lors de l\'affectation des %s séquences du schéma "%s" dans le groupe de tables "%s".';

	// Move sequences
	$lang['emajmovesequence'] = 'E-Maj : Déplacer des séquences dans un autre groupe de tables';
	$lang['emajconfirmmovesequence'] = 'Déplacer la sequence "%s.%s" de son groupe de tables "%s".';
	$lang['emajconfirmmovesequences'] = 'Déplacer ces %s séquences du schéma "%s" :';
	$lang['emajmovesequenceok'] = '%s séquence a été déplacée dans le groupe de tables %s.';
	$lang['emajmovesequencesok'] = '%s séquences ont été déplacées dans le groupe de tables %s.';
	$lang['emajmovesequenceerr'] = 'Erreur lors du déplacement de la séquence "%s.%s".';
	$lang['emajmovesequenceerr2'] = 'Erreur lors du déplacement de la séquence "%s.%s" du groupe de tables "%s" vers le groupe de tables "%s".';
	$lang['emajmovesequenceserr'] = 'Erreur lors du déplacement des %s séquences du schéma "%s".';
	$lang['emajmovesequenceserr2'] = 'Erreur lors du déplacement des %s séquences du schéma "%s" de leur groupe de tables vers le groupe de tables "%s".';

	// Remove sequences
	$lang['emajremovesequence'] = 'E-Maj : Retirer des séquences de leur groupe de tables';
	$lang['emajconfirmremovesequence'] = 'Retirer la séquence "%s.%s" de son groupe de tables "%s".';
	$lang['emajconfirmremovesequences'] = 'Retirer ces %s séquences du schéma "%s" :';
	$lang['emajremovesequenceok'] = '%s séquence a été retirée de son groupe de tables.';
	$lang['emajremovesequencesok'] = '%s séquences ont été retirées de leur groupe de tables.';
	$lang['emajremovesequenceerr'] = 'Erreur lors de la sortie de la sequence "%s.%s" du groupe de tables "%s".';
	$lang['emajremovesequenceserr'] = 'Erreur lors de la sortie des %s sequences du schéma "%s" de leur groupe de tables.';

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
	$lang['emajconfirmremove1tblseq'] = 'Êtes-vous sûr de vouloir retirer %s du groupe de tables "%s" ?';
	$lang['emajmodifygroupok'] = 'Le changement de configuration est enregistré. Il sera effectif après (re)création des groupes de tables concernés ou application des changements de configuration pour ces groupes.';

	// List Groups
	$lang['emajidlegroups'] = 'Groupes de tables en état "arrêté" ';
	$lang['emajlogginggroups'] = 'Groupes de tables en état "démarré" ';
	$lang['emajconfiguredgroups'] = 'Groupes de tables "configurés" mais non encore "créés" ';
	$lang['emajlogginggrouphelp'] = 'Quand un groupe de tables est dans l\'état \'démarré\', les insertions, modifications et suppression de lignes sur ses tables sont enregistrées.';
 	$lang['emajidlegrouphelp'] = 'Quand un groupe de tables est dans l\'état \'arrêté\', les insertions, modifications et suppressions de lignes sur ses tables ne sont PAS enregistrées.';
	$lang['emajconfiguredgrouphelp'] = 'La configuration d\'un groupe définit les tables et séquences qui vont le constituer. Une fois \'configuré\', le groupe doit être \'créé\', afin de préparer tous les objets nécessaires à son utilisation (tables de log, fonctions,...).';
	$lang['emajnbtbl'] = 'Tables';
	$lang['emajnbseq'] = 'Séquences';
	$lang['emajnbmark'] = 'Marques';
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
	$lang['emajdroppedgroupslist'] = 'Anciens groupes de tables supprimés';
	$lang['emajnodroppedgroup'] = 'Aucun ancien groupe de tables supprimé.';

	// Rollback activity
	$lang['emajrlbkid'] = 'Id. Rlbk';
	$lang['emajrlbkstart'] = 'Début rollback';
	$lang['emajrlbkend'] = 'Fin rollback';
	$lang['emajduration'] = 'Durée';
	$lang['emajislogged'] = 'Tracé ?';
	$lang['emajnbsession'] = 'Sessions';
	$lang['emajcurrentduration'] = 'Durée actuelle';
	$lang['emajglobalduration'] = 'Durée globale';
	$lang['emajplanningduration'] = 'Durée planification';
	$lang['emajlockingduration'] = 'Durée pose verrous';
	$lang['emajestimremaining'] = 'Restant estimée';
	$lang['emajpctcompleted'] = '% effectué';
	$lang['emajinprogressrlbk'] = 'Rollbacks E-Maj en cours';
	$lang['emajrlbkmonitornotavailable'] = 'Le suivi des rollbacks en cours n\'est pas disponible.';
	$lang['emajcompletedrlbk'] = 'Rollbacks E-Maj terminés';
	$lang['emajnbtabletoprocess'] = 'Tables à traiter';
	$lang['emajnbseqtoprocess'] = 'Séquences à traiter';
	$lang['emajnorlbk'] = 'Aucun rollback.';
	$lang['emajconsolidablerlbk'] = 'Rollbacks E-Maj tracés consolidables';
	$lang['emajtargetmark'] = 'Marque cible';
	$lang['emajendrollbackmark'] = 'Marque fin de rollback';
	$lang['emajnbintermediatemark'] = 'Marques intermédiaires';
	$lang['emajconsolidate'] = 'Consolider';
	$lang['emajconsolidaterlbk'] = 'Consolider un rollback tracé';
	$lang['emajconfirmconsolidaterlbk'] = 'Êtes-vous sûr de vouloir consolider le rollback terminé par la marque "%s" du groupe de tables "%s" ?';
	$lang['emajconsolidaterlbkok'] = 'Le rollback terminé par la marque "%s" du groupe de tables "%s" a été consolidé.';
	$lang['emajconsolidaterlbkerr'] = 'Erreur lors de la consolidation du rollback terminé par la marque "%s" du groupe de tables "%s" !';
	$lang['emajrlbkprogress'] = 'Progression du rollback';
	$lang['emajrlbksessions'] = 'Sessions';
	$lang['emajrlbksession'] = 'Session';
	$lang['emajrlbkexecreport'] = 'Rapport d\'exécution';
	$lang['emajrlbkplanning'] = 'Planification';
	$lang['emajrlbkplanninghelp'] = 'Les principales étapes élémentaires d\'exécution du Rollback E-Maj. Ne sont pas inclus : la planification et la pose des verrous sur les tables en début d\'opération et, pour les versions emaj < 4.2, le traitement des séquences en fin d\'opération.';
	$lang['emajrlbkestimmethodhelp'] = 'En phase de planification, la durée de chaque étape est estimée, en utilisant en priorité des statistiques d\'exécutions similaires passées, avec le même ordre de grandeur de quantités à traiter (STAT+), ou des ordres de grandeur différentes (STAT), ou, à défaut, les paramètres de l\'extension (PARAM). La colonne Q évalue la qualité des estimations de durée.';
	$lang['emajnorlbkstep'] = 'Pas d\'étape élémentaire pour ce rollback.';
	$lang['emajhideestimates'] = 'Cacher estimations';
	$lang['emajshowestimates'] = 'Voir estimations';
	$lang['emajrlbkstep'] = 'Étape';
	$lang['emajestimatedquantity'] = 'Quantité estimée';
	$lang['emajestimationmethod'] = 'Méthode estimation';
	$lang['emajrlbksequences'] = 'Effectuer le rollback des séquences';
	$lang['emajrlbkdisapptrg'] = 'Désactiver le trigger %s';
	$lang['emajrlbkdislogtrg'] = 'Désactiver le trigger de log';
	$lang['emajrlbksetalwaysapptrg'] = 'Passer le trigger %s à ALWAYS';
	$lang['emajrlbkdropfk'] = 'Supprimer la clé étrangère %s';
	$lang['emajrlbksetfkdef'] = 'Positionner la clé étrangère %s DEFFERED';
	$lang['emajrlbkrlbktable'] = 'Effectuer le rollback de la table';
	$lang['emajrlbkdeletelog'] = 'Supprimer des log';
	$lang['emajrlbksetfkimm'] = 'Positionner la clé étrangère %s IMMEDIATE';
	$lang['emajrlbkaddfk'] = 'Recréer la clé étrangère %s';
	$lang['emajrlbkenaapptrg'] = 'Réactiver le trigger %s';
	$lang['emajrlbksetlocalapptrg'] = 'Passer le trigger %s à LOCAL';
	$lang['emajrlbkenalogtrg'] = 'Réactiver le trigger de log';

	// Rollback comment
	$lang['emajcommentarollback'] = 'E-Maj : Enregistrer un commentaire pour un rollback';
	$lang['emajcommentrollback'] = 'Entrer, modifier ou supprimer un commentaire pour le rollback %s';
	$lang['emajcommentrollbackok'] = 'Le commentaire a été enregistré pour le rollback %s.';
	$lang['emajcommentrollbackerr'] = 'Erreur lors de l\'enregistrement du commentaire pour le rollback %s !';

	// Group's properties and marks
	$lang['emajgroupproperties'] = 'Propriétés du groupe de tables "%s"';
	$lang['emajcontent'] = 'Contenu';
	$lang['emajgroupmarks'] = 'Marques du groupe de tables "%s"';
	$lang['emajlogsessionshelp'] = 'Session de log, représentant l\'intervalle de temps entre le démarrage et l\'arrêt du groupe de tables.';
	$lang['emajlogsessionstart'] = 'Session de log démarrée à : %s';
	$lang['emajlogsessionstop'] = ' et arrêtée à : %s';
	$lang['emajtimestamp'] = 'Date-Heure';
	$lang['emajnbchanges'] = 'Mises à jour';
	$lang['emajcumchanges'] = 'Cumul mises<br>à jour';
	$lang['emajcumchangeshelp'] = 'Le cumul du nombre de mises à jour représente le nombre de mises à jour à annuler en cas de rollback E-Maj à la marque correspondante.';
	$lang['emajrlbk'] = 'Rollback';
	$lang['emajfirstmark'] = 'Première marque';
	$lang['emajrename'] = 'Renommer';
	$lang['emajnomark'] = 'Le groupe de tables n\'a pas de marque';
	$lang['emajprotect'] = 'Protéger';
	$lang['emajunprotect'] = 'Déprotéger';

	// Statistics
	$lang['emajchangesgroup'] = 'Mises à jour enregistrées pour le groupe de tables "%s"';
	$lang['emajcurrentsituation'] = 'Situation courante';
	$lang['emajestimatetables'] = 'Estimer tables';
	$lang['emajestimatesequences'] = 'Estimer séquences';
	$lang['emajdetailtables'] = 'Détailler tables';
	$lang['emajdetailedlogstatwarning'] = 'Le parcours des tables de log nécessaire à l\'obtention des statistiques détaillées peut être long. Bien que moins détaillée et moins précise, l\'estimation du nombre de mises à jour est plus rapide car elle n\'utilise que les valeurs des séquences de log enregistrées à chaque marque.';
	$lang['emajchangestblbetween'] = 'Mises à jour de table entre les marques "%s" et "%s"';
	$lang['emajchangestblsince'] = 'Mises à jour de table depuis la marque "%s"';
	$lang['emajtblingroup'] = 'Tables dans le groupe';
	$lang['emajtblwithchanges'] = 'Tables mises à jour';
	$lang['emajchangesseqbetween'] = 'Mises à jour de séquence entre les marques "%s" et "%s"';
	$lang['emajchangesseqsince'] = 'Mises à jour de séquence depuis la marque "%s"';
	$lang['emajseqingroup'] = 'Séquences dans le groupe';
	$lang['emajseqwithchanges'] = 'Séquences mises à jour';
	$lang['emajstatincrements'] = 'Incréments';
	$lang['emajstatstructurechanged'] = 'Changement structure ?';
	$lang['emajstatverb'] = 'Verbe SQL';
	$lang['emajnbinsert'] = 'INSERT';
	$lang['emajnbupdate'] = 'UPDATE';
	$lang['emajnbdelete'] = 'DELETE';
	$lang['emajnbtruncate'] = 'TRUNCATE';
	$lang['emajnbrole'] = 'Rôles';
	$lang['emajlogsessionwarning'] = 'Cet intervalle de marques couvre plusieurs sessions de log. Des mises à jour de données peuvent ne pas avoir été enregistrées.';
	$lang['emajstatrows'] = 'Mises à jour';
	$lang['emajbrowsechanges'] = 'Voir les mises à jour';

	// Dump changes SQL generation
	$lang['emajsqlgentitle'] = 'Générer la requête SQL d\'extraction des mises à jour';
	$lang['emajsqlgenmarksinterval'] = 'Intervalle de marques';
	$lang['emajsqlgennopk'] = 'La table n\'a pas de clé primaire. Aucune vision consolidée des mises à jour n\'est possible';
	$lang['emajsqlgenconsolidation'] = 'Consolidation';
	$lang['emajsqlgenconsonone'] = 'Aucune';
	$lang['emajsqlgenconsopartial'] = 'Partielle';
	$lang['emajsqlgenconsofull'] = 'Complète';
	$lang['emajsqlgenconsohelp'] = 'Sans consolidation, toutes les mises à jour élémentaires enregistrées dans la table de log, pour la tranche de marques sélectionnée, sont restituées. Avec une consolidation (partielle ou totale), ne sont restitués que l\'état initial et/ou l\'état final de chaque clé primaire. Avec une consolidation totale, aucune donnée n\'est restituée lorsque l\'état initial et l\'état final de la ligne sont strictement identiques.';
	$lang['emajsqlgenverbs'] = 'Verbes SQL';
	$lang['emajsqlgenverbshelp'] = 'Lorsqu\'il n\'y a pas de consolidation, il est possible de filtrer les mises à jour restituées sur les verbes SQL.';
	$lang['emajsqlgenknownroles'] = 'Rôles connus :';
	$lang['emajsqlgenroleshelp'] = 'Lorsqu\'il n\'y a pas de consolidation, il est possible de filtrer les mises à jour restituées sur les rôles à l\'origine des mises à jour. Si alimenté, le champs doit contenir une liste de rôles séparés par des virgules.';
	$lang['emajsqlgentechcols'] = 'Colonnes techniques E-Maj';
	$lang['emajsqlgentechcolshelp'] = 'Dans le résultat de la requête, la plupart des colonnes techniques E-Maj peuvent être masquées.';
	$lang['emajsqlgencolsorder'] = 'Ordre des colonnes';
	$lang['emajsqlgencolsorderlog'] = 'Comme la table de log';
	$lang['emajsqlgencolsorderpk'] = 'Clé primaire en tête';
	$lang['emajsqlgencolsorderhelp'] = 'Dans le résultat de la requête, soit les colonnes restituées sont dans le même ordre que la table de log, soit les colonnes de la clé primaire (celles de la table applicative + emaj_tuple) sont placées en tête.';
	$lang['emajsqlgenroworder'] = 'Ordre des lignes';
	$lang['emajsqlgenrowordertime'] = 'Chronologique';
	$lang['emajsqlgenroworderhelp'] = 'Dans le résultat de la requête, les lignes peuvent être présentées dans l\'ordre chronologique d\'enregistrement des mises à jour ou dans l\'ordre des clés primaires.';
	$lang['emajsqlgenerate'] = 'Générer le SQL';

	// Group's content
	$lang['emajgroupcontent'] = 'Contenu actuel du groupe de tables "%s"';
	$lang['emajemptygroup'] = 'Le groupe de tables "%s" est actuellement vide.';
	$lang['emajpriority'] = 'Priorité';
	$lang['emajlogtable'] = 'Table de log';

	// Group's history
	$lang['emajgrouphistory'] = 'Historique du groupe de tables "%s"';
	$lang['emajnohistory'] = 'Il n\'y a aucun historique à afficher pour ce groupe.';
	$lang['emajgrouphistoryorder'] = 'Les plus récentes créations de groupe, suppressions de groupe et sessions de log sont placées en début de tableau.';
	$lang['emajnblogsessions'] = 'Sessions de log';
	$lang['emajgroupcreate'] = 'Création du groupe';
	$lang['emajgroupdrop'] = 'Suppression du groupe';
	$lang['emajdeletedlogsessions'] = 'Des sessions de log supprimées';

	// Generic error messages for groups and marks checks
	$lang['emajgroupmissing'] = 'Le groupe de tables "%s" n\'existe plus.';
	$lang['emajgroupsmissing'] = '%s groupes de tables (%s) n\'existent plus.';
	$lang['emajgroupalreadyexists'] = 'Le groupe de table "%s" existe déjà.';
	$lang['emajgroupstillexists'] = 'Le groupe de table "%s" existe toujours.';
	$lang['emajgroupnotstopped'] = 'Le groupe de tables "%s" n\'est plus arrêté.';
	$lang['emajgroupsnotstopped'] = '%s groupes de tables (%s) ne sont plus arrêtés.';
	$lang['emajgroupnotstarted'] = 'Le groupe de tables "%s" n\'est plus démarré.';
	$lang['emajgroupsnotstarted'] = '%s groupes de tables (%s) ne sont plus démarrés.';
$lang['emajgroupprotected'] = 'Le groupe de table "%s" est protégé.';
	$lang['emajgroupsprotected'] = '%s groupes de tables (%s) sont protégés.';
	$lang['emajinvalidmark'] = 'La marque saisie (%s) est invalide.';
	$lang['emajduplicatemarkgroup'] = 'La marque "%s" existe déjà dans le groupe de tables "%s".';
	$lang['emajduplicatemarkgroups'] = 'La marque "%s" existe déjà dans %s groupes de tables (%s).';
	$lang['emajmarkmissing'] = 'La marque "%s" n\'existe plus.';
	$lang['emajmarksmissing'] = '%s marques (%s) n\'existent plus.';
	$lang['emajmissingmarkgroup'] = 'La marque n\'existe plus dans le groupe de tables "%s".';
	$lang['emajmissingmarkgroups'] = 'La marque n\'existe plus dans %s groupes de tables (%s).';
	$lang['emajadoreturncode'] = 'Code retour de la couche ADO = %s.';

	// Group creation
	$lang['emajcreateagroup'] = 'E-Maj : Créer un groupe de tables';
	$lang['emajconfirmcreategroup'] = 'Êtes-vous sûr de vouloir créer le groupe de tables "%s" ?';
	$lang['emajcreategroupok'] = 'Le groupe de tables "%s" a été créé.';
	$lang['emajcreategrouperr'] = 'Erreur lors de la création du groupe de tables "%s".';

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
	$lang['emajcheckconfgroups15'] = 'Dans le groupe "%s", pour la table "%s.%s", le trigger "%s" n\'existe pas.';
	$lang['emajcheckconfgroups16'] = 'Dans le groupe "%s", pour la table "%s.%s", le trigger "%s" est un trigger E-Maj.';
	$lang['emajcheckconfgroups20'] = 'Dans le groupe "%s", la table "%s.%s" est une table UNLOGGED.';
	$lang['emajcheckconfgroups21'] = 'Dans le groupe "%s", la table "%s.%s" est déclarée WITH OIDS.';
	$lang['emajcheckconfgroups22'] = 'Dans le groupe "%s", la table "%s.%s" n\'a pas de PRIMARY KEY.';
	$lang['emajcheckconfgroups30'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le suffixe de schéma secondaire de log n\'est pas NULL.';
	$lang['emajcheckconfgroups31'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le préfixe des noms E-Maj n\'est pas NULL.';
	$lang['emajcheckconfgroups32'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le tablespace de la table de log n\'est pas NULL.';
	$lang['emajcheckconfgroups33'] = 'Dans le groupe "%s", pour la séquence "%s.%s", le tablespace de l\'index de log n\'est pas NULL.';

	// Group drop
	$lang['emajdropagroup'] = 'E-Maj : Supprimer un groupe de tables';
	$lang['emajconfirmdropgroup'] = 'Êtes-vous sûr de vouloir supprimer le groupe de tables "%s" ?';
	$lang['emajdropgroupok'] = 'Le groupe de tables "%s" a été supprimé.';
	$lang['emajdropgrouperr'] = 'Erreur lors de la suppression du groupe de tables "%s".';

	// Groups drop
	$lang['emajdropgroups'] = 'E-Maj : Supprimer les groupes de tables';
	$lang['emajconfirmdropgroups'] = 'Êtes-vous sûr de vouloir supprimer les groupes de tables "%s" ?';
	$lang['emajdropgroupsok'] = 'Les groupes de tables "%s" ont été supprimés.';
	$lang['emajdropgroupserr'] = 'Erreur lors de la suppression des groupes de tables "%s".';

	// Group forget
	$lang['emajforgetagroup'] = 'E-Maj : Effacer un groupe de tables des historiques';
	$lang['emajconfirmforgetgroup'] = 'Êtes-vous sûr de vouloir effacer le groupe de tables "%s" des historiques ?';
	$lang['emajforgetgroupok'] = 'Le groupe de tables "%s" a été effacé des historiques.';
	$lang['emajforgetgrouperr'] = 'Erreur lors de l\'effacement du groupe de tables "%s" des historiques.';

	// Export groups configuration
	$lang['emajexportgroupsconf'] = 'Exporter une configuration de groupes de tables';
	$lang['emajexportgroupsconfselect'] = 'Sélectionnez les groupes de tables dont la configuration sera exportée sur un fichier local.';
	$lang['emajexportgroupserr'] = 'Erreur lors de l\'exportation des groupes de tables "%s".';

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
	$lang['emajcheckjsongroupsconf226'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, le trigger #%s n\'est pas une chaîne de caractères.';
	$lang['emajcheckjsongroupsconf227'] = 'Dans le groupe de tables "%s" et pour la table %s.%s, l\'attribut "ignored_triggers" n\'est pas un tableau.';
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
	$lang['emajaltergroups'] = 'E-Maj : Appliquer les changements de configuration';
	$lang['emajalteraloggingroup'] = 'Le groupe "%s" est actif. Vous pouvez spécifier un nom de marque.';
	$lang['emajconfirmaltergroup'] = 'Êtes-vous sûr de vouloir appliquer les changements de configuration pour le groupe de tables "%s" ?';
	$lang['emajcantaltergroup'] = 'La modification du groupe "%s" générerait des actions qui ne peuvent être effectuées sur un groupe actif. Arrêtez le groupe avant de le modifier.';
	$lang['emajaltergroupok'] = 'Les changements de configuration du groupe de tables "%s" ont été appliqués.';
	$lang['emajaltergrouperr'] = 'Erreur lors de l\'application des changements de configuration pour le groupe de tables "%s" !';

	// Groups alter
	$lang['emajalterallloggingroups'] = 'Les groupes "%s" sont actifs. Vous pouvez spécifier un nom de marque.';
	$lang['emajconfirmaltergroups'] = 'Êtes-vous sûr de vouloir appliquer les changements de configuration pour les groupes de tables "%s" ?';
	$lang['emajaltergroupsok'] = 'Les changements de configuration des groupes de tables "%s" ont été appliqués.';
	$lang['emajaltergroupserr'] = 'Erreur lors de l\'application des changements de configuration pour les groupes de tables "%s" !';

	// Group comment
	$lang['emajcommentagroup'] = 'E-Maj : Enregistrer un commentaire pour un groupe de tables ';
	$lang['emajcommentgroup'] = 'Entrer, modifier ou supprimer un commentaire pour le groupe de tables "%s"';
	$lang['emajcommentgroupok'] = 'Le commentaire a été enregistré pour le groupe de tables "%s".';
	$lang['emajcommentgrouperr'] = 'Erreur lors de l\'enregistrement du commentaire pour le groupe de tables "%s".';

	// Group start
	$lang['emajstartagroup'] = 'E-Maj : Démarrer un groupe de tables';
	$lang['emajconfirmstartgroup'] = 'Démarrage du groupe de tables "%s"';
	$lang['emajinitmark'] = 'Marque initiale';
	$lang['emajoldlogsdeletion'] = 'Suppression des anciens logs';
	$lang['emajstartgroupok'] = 'Le groupe de tables "%s" est démarré avec la marque "%s".';
	$lang['emajstartgrouperr'] = 'Erreur lors du démarrage du groupe de tables "%s".';
	$lang['emajstartgrouperr2'] = 'Erreur lors du démarrage du groupe de tables "%s" avec la marque "%s".';

	// Groups start
	$lang['emajstartgroups'] = 'E-Maj : Démarrer des groupes de tables';
	$lang['emajconfirmstartgroups'] = 'Démarrage des groupes de tables "%s"';
	$lang['emajstartgroupsok'] = 'Les groupes de tables "%s" ont été démarrés avec la marque "%s".';
	$lang['emajstartgroupserr'] = 'Erreur lors du démarrage des groupes de tables "%s".';
	$lang['emajstartgroupserr2'] = 'Erreur lors du démarrage des groupes de tables "%s" avec la marque "%s".';

	// Group stop
	$lang['emajstopagroup'] = 'E-Maj : Arrêter un groupe de tables ';
	$lang['emajconfirmstopgroup'] = 'Arrêt du groupe de tables "%s"';
	$lang['emajstopmark'] = 'Marque finale';
	$lang['emajforcestop'] = 'Forcer l\'arrêt (en cas de problème seulement)';
	$lang['emajstopgroupok'] = 'Le groupe de tables "%s" a été arrêté.';
	$lang['emajstopgrouperr'] = 'Erreur lors de l\'arrêt du groupe de tables "%s".';
	$lang['emajstopgrouperr2'] = 'Erreur lors de l\'arrêt du groupe de tables "%s" avec la marque "%s".';

	// Groups stop
	$lang['emajstopgroups'] = 'E-Maj : Arrêter des groupes de tables';
	$lang['emajconfirmstopgroups'] = 'Arrêt des groupes de tables "%s"';
	$lang['emajstopgroupsok'] = 'Les groupes de tables "%s" ont été arrêtés.';
	$lang['emajstopgroupserr'] = 'Erreur lors de l\'arrêt des groupes de tables "%s".';
	$lang['emajstopgroupserr2'] = 'Erreur lors de l\'arrêt des groupes de tables "%s" avec la marque "%s".';

	// Group reset
	$lang['emajresetagroup'] = 'E-Maj : Réinitialiser un groupe de tables';
	$lang['emajconfirmresetgroup'] = 'Êtes-vous sûr de vouloir réinitialiser le groupe de tables "%s" ?';
	$lang['emajresetgroupok'] = 'Le groupe de tables "%s" est réinitialisé.';
	$lang['emajresetgrouperr'] = 'Erreur lors de la réinitialisation du groupe de tables "%s".';

	// Groups reset
	$lang['emajresetgroups'] = 'E-Maj : Réinitialiser des groupes de tables';
	$lang['emajconfirmresetgroups'] = 'Êtes-vous sûr de vouloir réinitialiser les groupe de tables "%s" ?';
	$lang['emajresetgroupsok'] = 'Les groupes de tables "%s" ont été réinitialisés.';
	$lang['emajresetgroupserr'] = 'Erreur lors de la réinitialisation des groupes de tables "%s".';

	// Group protect
	$lang['emajprotectgroupok'] = 'Le groupe de tables "%s" est maintenant protégé contre les rollbacks.';
	$lang['emajprotectgrouperr'] = 'Erreur lors de la protection du groupe de tables "%s".';

	// Groups protect
	$lang['emajprotectgroupsok'] = 'Les groupes de tables "%s" sont maintenant protégés contre les rollbacks.';
	$lang['emajprotectgroupserr'] = 'Erreur lors de la protection des groupes de tables "%s".';

	// Group unprotect
	$lang['emajunprotectgroupok'] = 'Le groupe de tables "%s" est maintenant déprotégé.';
	$lang['emajunprotectgrouperr'] = 'Erreur lors de la déprotection du groupe de tables "%s".';

	// Groups unprotect
	$lang['emajunprotectgroupsok'] = 'Les groupes de tables "%s" sont maintenant déprotégés.';
	$lang['emajunprotectgroupserr'] = 'Erreur lors de la déprotection des groupes de tables "%s".';

	// Set Mark for one group
	$lang['emajsetamark'] = 'E-Maj : Poser une marque';
	$lang['emajconfirmsetmarkgroup'] = 'Pose d\'une marque pour le groupe de tables "%s" :';
	$lang['emajsetmarkgroupok'] = 'La marque "%s" est posée pour le groupe de tables "%s".';
	$lang['emajsetmarkgrouperr'] = 'Erreur lors de la pose d\'une marque pour le groupe de tables "%s".';
	$lang['emajsetmarkgrouperr2'] = 'Erreur lors de la pose de la marque "%s" pour le groupe de tables "%s".';

	// Set Mark for several groups
	$lang['emajconfirmsetmarkgroups'] = 'Pose d\'une marque pour les groupes de tables "%s" :';
	$lang['emajsetmarkgroupsok'] = 'La marque "%s" est posée pour les groupes de tables "%s".';
	$lang['emajsetmarkgroupserr'] = 'Erreur lors de la pose d\'une marque pour les groupes de tables "%s".';
	$lang['emajsetmarkgroupserr2'] = 'Erreur lors de la pose de la marque "%s" pour les groupes de tables "%s".';

	// Group rollback
	$lang['emajrlbkagroup'] = 'E-Maj : Rollbacker un groupe de tables';
	$lang['emajconfirmrlbkgroup'] = 'Rollback du groupe de tables "%s" à la marque "%s"';
	$lang['emajunknownestimate'] = 'non connue';
	$lang['emajdurationminutesseconds'] = '%s min %s s';
	$lang['emajdurationhoursminutes'] = '%s h %s min';
	$lang['emajdurationovertendays'] = '> 10 jours';
	$lang['emajselectmarkgroup'] = 'Rollback du groupe de tables "%s" à la marque : ';
	$lang['emajrlbkthenmonitor'] = 'Rollback et suivi';
	$lang['emajcantrlbkinvalidmarkgroup'] = 'La marque "%s" n\'est pas valide.';
	$lang['emajreachaltergroup'] = 'Le rollback du groupe de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification du groupe. Veuillez confirmer le rollback.';
	$lang['emajautorolledback'] = 'Annulé automatiquement ?';
	$lang['emajrlbkgrouperr'] = 'Erreur lors du rollback du groupe de tables "%s".';
	$lang['emajrlbkgrouperr2'] = 'Erreur lors du rollback du groupe de tables "%s" à la marque "%s".';
	$lang['emajestimrlbkgrouperr'] = 'Erreur lors de l\'estimation de la durée de rollback du groupe de tables "%s" à la marque "%s".';
	$lang['emajbadconfparam'] = 'Erreur : le rollback asynchrone n\'est plus possible. Vérifiez l\'existence de l\'extension dblink et la valeur des deux paramètres de configuration du chemin de la commande psql (%s) et du répertoire temporaire (%s).';
	$lang['emajasyncrlbkstarted'] = 'Rollback #%s démarré.';
	$lang['emajrlbkgroupreport'] = 'Rapport d\'exécution du rollback du groupe de tables "%s" à la marque "%s"';

	// Groups rollback
	$lang['emajrlbkgroups'] = 'E-Maj : Rollbacker des groupes de tables';
	$lang['emajselectmarkgroups'] = 'Rollback des groupes de tables "%s" à la marque : ';
	$lang['emajnomarkgroups'] = 'Aucune marque commune aux groupes de tables "%s" ne peut être utilisée pour un rollback.';
	$lang['emajcantrlbkinvalidmarkgroups'] = 'Le rollback des groupes de tables "%s" est impossible. La marque "%s" n\'est pas valide.';
	$lang['emajreachaltergroups'] = 'Le rollback des groupes de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification de groupes. Veuillez confirmer le rollback.';
	$lang['emajrlbkgroupserr'] = 'Erreur lors du rollback des groupes de tables "%s".';
	$lang['emajrlbkgroupserr2'] = 'Erreur lors du rollback des groupes de tables "%s" à la marque "%s".';
	$lang['emajestimrlbkgroupserr'] = 'Erreur lors de l\'estimation de la durée de rollback des groupes de tables "%s" à la marque "%s".';
	$lang['emajrlbkgroupsreport'] = 'Rapport d\'exécution du rollback des groupes de tables "%s" à la marque "%s"';

	// Elementary alter group actions previously executed, reported at rollback time 
	$lang['emajalteredremovetbl'] = 'La table "%s.%s" a été supprimée du groupe de tables "%s"';
	$lang['emajalteredremoveseq'] = 'La séquence "%s.%s" a été supprimée du groupe de tables "%s"';
	$lang['emajalteredrepairtbl'] = 'Les objets E-Maj pour la table "%s.%s" ont été reparés';
	$lang['emajalteredrepairseq'] = 'Les objets E-Maj pour la séquence "%s.%s" ont été reparés';
	$lang['emajalteredchangetbllogschema'] = 'Le schéma de log E-Maj pour la table "%s.%s" a été modifié';
	$lang['emajalteredchangetblnamesprefix'] = 'Le préfixe des noms E-Maj pour la table "%s.%s" a été modifié';
	$lang['emajalteredchangetbllogdatatsp'] = 'Le tablespace pour le log de la table "%s.%s" a été modifié';
	$lang['emajalteredchangetbllogindextsp'] = 'Le tablespace pour les index de log de la table "%s.%s" a été modifié';
	$lang['emajalteredchangerelpriority'] = 'La priorité E-Maj pour la table "%s.%s" a été modifiée';
	$lang['emajalteredchangeignoredtriggers'] = 'Les triggers à ignorer au rollback de la table "%s.%s" ont été modifiés';
	$lang['emajalteredmovetbl'] = 'La table "%s.%s" a été déplacée du groupe de tables "%s" vers le groupe de tables "%s"';
	$lang['emajalteredmoveseq'] = 'La séquence "%s.%s" a été déplacée du groupe de tables "%s" vers le groupe de tables "%s"';
	$lang['emajalteredaddtbl'] = 'La table "%s.%s" a été ajoutée au groupe de tables "%s"';
	$lang['emajalteredaddseq'] = 'La séquence "%s.%s" a été ajoutée au groupe de tables "%s"';

	// Protect mark
	$lang['emajprotectmarkok'] = 'La marque "%s" du groupe de tables "%s" est maintenant protégée contre les rollbacks.';
	$lang['emajprotectmarkerr'] = 'Erreur lors de la protection de la marque "%s" du groupe de tables "%s".';

	// Unprotect mark
	$lang['emajunprotectmarkok'] = 'La marque "%s" du groupe de tables "%s" est maintenant déprotégée.';
	$lang['emajunprotectmarkerr'] = 'Erreur lors de la déprotection de la marque "%s" du groupe de tables "%s".';

	// Comment mark
	$lang['emajcommentamark'] = 'E-Maj : Enregistrer un commentaire pour une marque';
	$lang['emajcommentmark'] = 'Entrer, modifier ou supprimer le commentaire pour la marque "%s" du groupe de tables "%s".';
	$lang['emajcommentmarkok'] = 'Le commentaire a été enregistré pour la marque "%s" du groupe de tables "%s".';
	$lang['emajcommentmarkerr'] = 'Erreur lors de l\'enregistrement du commentaire pour la marque "%s" du groupe de tables "%s".';

	// Mark renaming
	$lang['emajrenameamark'] = 'E-Maj : Renommer une marque';
	$lang['emajconfirmrenamemark'] = 'Renomage de la marque "%s" du groupe de tables "%s"';
	$lang['emajnewnamemark'] = 'Nouveau nom';
	$lang['emajrenamemarkok'] = 'La marque "%s" du groupe de tables "%s" a été renommée en "%s".';
	$lang['emajrenamemarkerr'] = 'Erreur lors du renommage de la marque "%s" du groupe de tables "%s".';
	$lang['emajrenamemarkerr2'] = 'Erreur lors du renommage de la marque "%s" du groupe de tables "%s" en "%s".';

	// Mark deletion
	$lang['emajdeleteamark'] = 'E-Maj : Effacer une marque';
	$lang['emajconfirmdeletemark'] = 'Êtes-vous sûr de vouloir effacer la marque "%s" pour le groupe de tables "%s" ?';
	$lang['emajdeletemarkok'] = 'La marque "%s" a été effacée pour le groupe de tables "%s".';
	$lang['emajdeletemarkerr'] = 'Erreur lors de l\'effacement de la marque "%s" pour le groupe de tables "%s".';

	// Marks deletion
	$lang['emajdeletemarks'] = 'E-Maj : Effacer des marques';
	$lang['emajconfirmdeletemarks'] = 'Êtes-vous sûr de vouloir effacer ces %s marques pour le groupe de tables "%s" ?';
	$lang['emajdeletemarksok'] = 'Les %s marques ont été effacées pour le groupe de tables "%s".';
	$lang['emajdeletemarkserr'] = 'Erreur lors de l\'effacement des marques "%s" pour le groupe de tables "%s".';

	// Marks before mark deletion
	$lang['emajdelmarksprior'] = 'E-Maj : Supprimer des marques';
	$lang['emajconfirmdelmarksprior'] = 'Êtes-vous sûr de vouloir supprimer toutes les marques et log antérieurs à la marque "%s" pour le groupe de tables "%s" ?';
	$lang['emajdelmarkspriorok'] = 'Les (%s) marques antérieures à la marque "%s" ont été supprimées pour le groupe de tables "%s".';
	$lang['emajdelmarkspriorerr'] = 'Erreur lors de la suppression des marques antérieures à la marque "%s" pour le groupe de tables "%s".';

?>
