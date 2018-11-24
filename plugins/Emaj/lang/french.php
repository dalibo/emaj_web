<?php

	/**
	 * french language file for E-maj extension of phpPgAdmin.
	 */

	// Basic strings
	$plugin_lang['emajplugin'] = 'Plugin E-Maj';
	$plugin_lang['emajnotavail'] = 'Désolé, E-Maj n\'est pas disponible ou accessible sur cette base de données. Plus de détails sur l\'onglet %s.';
	$plugin_lang['emajstate'] = 'Etat';
	$plugin_lang['emajnoselectedgroup'] = 'Aucun groupe de tables n\'a été sélectionné !';
	$plugin_lang['emajgroup'] = 'Groupe';
	$plugin_lang['emajgroups'] = 'Groupes';
	$plugin_lang['emajmark'] = 'Marque';
	$plugin_lang['emajmarks'] = 'Marques';
	$plugin_lang['emajgrouptype'] = 'Type de groupe';
	$plugin_lang['emajrollbacktype'] = 'Type de rollback';
	$plugin_lang['emajauditonly'] = 'Audit-seul';
	$plugin_lang['emajrollbackable'] = 'Rollbackable';
	$plugin_lang['emajunlogged'] = 'non tracé';
	$plugin_lang['emajlogged'] = 'tracé';
	$plugin_lang['emajlogging'] = 'Démarré';
	$plugin_lang['emajidle'] = 'Arrêté';
	$plugin_lang['emajactive'] = 'Active';
	$plugin_lang['emajdeleted'] = 'Supprimée';
	$plugin_lang['emajprotected'] = 'Protégé contre les rollbacks';
	$plugin_lang['emajpagebottom'] = 'Bas de la page';
	$plugin_lang['emajselect'] = 'Sélectionner';
	$plugin_lang['emajactionsonselectedobjects'] = 'Actions sur les objets sélectionnés (0)';
	$plugin_lang['emajlogsize'] = 'Taille du log';
	$plugin_lang['emajrequiredfield'] = 'Champ requis';
	$plugin_lang['emajestimates'] = 'Estimations';
	$plugin_lang['emajfrom'] = 'De';
	$plugin_lang['emajto'] = 'A';
	$plugin_lang['emajall'] = 'Tous';
	$plugin_lang['emajnone'] = 'Aucun';
	$plugin_lang['emajinvert'] = 'Inverser';

	// E-Maj tabs
	$plugin_lang['emajenvir'] = 'Environnement E-Maj';
	$plugin_lang['emajgroupsconf'] = 'Configuration groupes';
	$plugin_lang['emajrlbkop'] = 'Rollbacks';
	$plugin_lang['emajlogstat'] = 'Statistiques log';

	// Common help messages
	$plugin_lang['emajmarknamehelp'] = 'Le nom de la marque doit être unique pour le groupe. Un caractère % représente l\'heure courante (format hh.mn.ss.ms).';
	$plugin_lang['emajmarknamemultihelp'] = 'Le nom de la marque doit être unique pour les groupes concernés. Un caractère % représente l\'heure courante (format hh.mn.ss.ms).';

	// E-Maj environment
	$plugin_lang['emajenvironment'] = 'Environnement E-Maj';
	$plugin_lang['emajextnotavailable'] = 'Le logiciel E-Maj n\'est pas installé sur cette instance PostgreSQL. Contactez votre administrateur des bases de données.';
	$plugin_lang['emajextnotcreated'] = 'L\'extension emaj n\'est pas créée dans cette base de données. Contactez votre administrateur des bases de données.';
	$plugin_lang['emajnogrant'] = 'Votre rôle de connexion n\'a pas les droits d\'utilisation d\'E-Maj. Utilisez un autre rôle ou contactez votre administrateur des bases de données.';
	$plugin_lang['emajcharacteristics'] = 'Caractéristiques';
	$plugin_lang['emajversions'] = 'Versions';
	$plugin_lang['emajversion'] = 'Version E-Maj : ';
	$plugin_lang['emajasextension'] = 'installée comme extension';
	$plugin_lang['emajasscript'] = 'installée par script';
	$plugin_lang['emajtooold'] = 'Désolé, cette version d\'E-Maj (%s) est trop ancienne. La version minimum supportée par ce plugin est %s.';
	$plugin_lang['emajversionmorerecent'] = 'Une version plus récente d\'E-Maj existe. Contactez votre administrateur des bases de données.';
	$plugin_lang['emajwebversionmorerecent'] = 'Une version plus récente d\'Emaj_web existe probablement. Contactez votre administrateur des bases de données.';
	$plugin_lang['emajdiskspace'] = 'Place disque occupée par l\'environnement E-Maj : %s de la base de données courante.';
	$plugin_lang['emajchecking'] = 'Intégrité de l\'environnement E-Maj';
	$plugin_lang['emajdiagnostics'] = 'Diagnostics';

	// Groups' content setup
	$plugin_lang['emajgroupsconfiguration'] = 'Configuration des groupes de tables';
	$plugin_lang['emajschemaslist'] = 'Liste des schémas applicatifs';
	$plugin_lang['emajunknownobject'] = 'Cet objet est référencé dans la table emaj_group_def mais n\'est pas créé.';
	$plugin_lang['emajunsupportedobject'] = 'Ce type d\'objet n\'est pas supporté par E-Maj (unlogged table, table avec OIDS, table partitionnée,...).';
	$plugin_lang['emajtblseqofschema'] = 'Tables et séquences du schéma "%s"';
	$plugin_lang['emajassign'] = 'Affecter';
	$plugin_lang['emajremove'] = 'Retirer';
	$plugin_lang['emajlogschemasuffix'] = 'Suffixe schéma log';
	$plugin_lang['emajlogdattsp'] = 'Tablespace log';
	$plugin_lang['emajlogidxtsp'] = 'Tablespace index log';
	$plugin_lang['emajspecifytblseqtoassign'] = 'Spécifiez au moins une table ou séquence à affecter';
	$plugin_lang['emajtblseqyetgroup'] = 'Erreur, "%s.%s" est déjà affecté à un groupe de tables.';
	$plugin_lang['emajtblseqbadtype'] = 'Erreur, le type de "%s.%s" n\'est pas supporté par E-Maj.';
	$plugin_lang['emajassigntblseq'] = 'E-Maj : Affecter des tables / séquences à un groupe de tables';
	$plugin_lang['emajconfirmassigntblseq'] = 'Affecter :';
	$plugin_lang['emajthetable'] = 'la table "%s.%s"';
	$plugin_lang['emajthesequence'] = 'la séquence "%s.%s"';
	$plugin_lang['emajfromgroup'] = 'du groupe "%s"';
	$plugin_lang['emajenterpriority'] = 'Priorité de traitement';
	$plugin_lang['emajpriorityhelp'] = 'Les tables et séquences sont traitées par ordre croissant de priorité, et par ordre alphabétique de nom si aucune priorité n\'est définie.';
	$plugin_lang['emajenterlogschema'] = 'Suffixe du schéma de log';
	$plugin_lang['emajlogschemahelp'] = 'Un schéma de log contient des tables, séquences et fonctions de log. Le schéma de log par défaut est \'emaj\'. Si un suffixe est défini pour la table, ses objets iront dans le schéma \'emaj\' + suffixe.';
	$plugin_lang['emajenternameprefix'] = 'Préfixe des noms d\'objets E-Maj';
	$plugin_lang['emajnameprefixhelp'] = 'Par défaut les noms des objets de log sont préfixés par &lt;schéma&gt;_&lt;table&gt;. Mais on peut définir un autre préfixe pour la table. Il doit être unique dans la base de données.';
	$plugin_lang['emajenterlogdattsp'] = 'Tablespace pour la table de log';
	$plugin_lang['emajenterlogidxtsp'] = 'Tablespace pour l\'index de la table de log';
	$plugin_lang['emajspecifytblseqtoupdate'] = 'Spécifiez au moins une table ou séquence à modifier';
	$plugin_lang['emajupdatetblseq'] = 'E-Maj : Modifier les propriétés d\'une table / séquence dans un groupe de tables';
	$plugin_lang['emajspecifytblseqtoremove'] = 'Spécifiez au moins une table ou séquence à retirer';
	$plugin_lang['emajtblseqnogroup'] = 'Erreur, "%s.%s" n\'est actuellement affecté à aucun groupe de tables.';
	$plugin_lang['emajremovetblseq'] = 'E-Maj : Retirer des tables / séquences de groupes de tables';
	$plugin_lang['emajconfirmremove1tblseq'] = 'Etes-vous sûr de vouloir retirer %s du groupe de tables "%s" ?';
	$plugin_lang['emajconfirmremovetblseq'] = 'Etes-vous sûr de vouloir retirer :';
	$plugin_lang['emajmodifygroupok'] = 'Le changement de configuration est enregistré. Il sera effectif après (re)création des groupes de tables concernés ou application des changements de configuration pour ces groupes.';
	$plugin_lang['emajmodifygrouperr'] = 'Erreur lors du changement de composition des groupes de tables.';

	// List Groups
	$plugin_lang['emajgrouplist'] = 'Liste des groupes de tables';
	$plugin_lang['emajidlegroups'] = 'Groupes de tables en état "arrêté" ';
	$plugin_lang['emajlogginggroups'] = 'Groupes de tables en état "démarré" ';
	$plugin_lang['emajconfiguredgroups'] = 'Groupes de tables "configurés" mais non encore "créés" ';
	$plugin_lang['emajlogginggrouphelp'] = 'Quand un groupe de tables est dans l\'état \'démarré\', les insertions, modifications et suppression de lignes sur ses tables sont enregistrées.';
 	$plugin_lang['emajidlegrouphelp'] = 'Quand un groupe de tables est dans l\'état \'arrêté\', les insertions, modifications et suppressions de lignes sur ses tables ne sont PAS enregistrées.';
	$plugin_lang['emajconfiguredgrouphelp'] = 'La configuration d\'un groupe définit les tables et séquences qui vont le constituer. Une fois \'configuré\', le groupe doit être \'créé\', afin de préparer tous les objets nécessaires à son utilisation (tables de log, fonctions,...).';
	$plugin_lang['emajcreationdatetime'] = 'Date/heure de création';
	$plugin_lang['emajnbtbl'] = 'Nb tables';
	$plugin_lang['emajnbseq'] = 'Nb séquences';
	$plugin_lang['emajnbmark'] = 'Nb marques';
	$plugin_lang['emajdetail'] = 'Détail';	
	$plugin_lang['emajsetmark'] = 'Poser une marque';
	$plugin_lang['emajsetcomment'] = 'Commenter';
	$plugin_lang['emajApplyConfChanges'] = 'Appliquer changements conf';
	$plugin_lang['emajnoidlegroup'] = 'Il n\'y a actuellement aucun groupe de tables en état "arrêté".';
	$plugin_lang['emajnologginggroup'] = 'Il n\'y a actuellement aucun groupe de tables en état "démarré".';
	$plugin_lang['emajnoconfiguredgroups'] = 'Il n\'y a actuellement aucun groupe de tables configuré mais non créé.';
	$plugin_lang['emajnoschema'] = 'Schéma inexistant (x%s) / ';
	$plugin_lang['emajinvalidschema'] = 'Schéma invalide (x%s) / ';
	$plugin_lang['emajnorelation'] = 'Table ou séquence inexistante (x%s) / ';
	$plugin_lang['emajinvalidtable'] = 'Type de table invalide (x%s) / ';
	$plugin_lang['emajduplicaterelation'] = 'Table ou séquence déjà affectée à un groupe (x%s) / ';
	$plugin_lang['emajnoconfiguredgroup'] = 'Pour créer un (autre) groupe de tables, allez d\'abord dans l\'onglet de configuration des groupes.<br>Vous pouvez aussi créer un groupe vide puis y ajouter des tables et séquences puis appliquer le changement de configuration.';
	$plugin_lang['emajcreateemptygroup'] = 'Créer un groupe vide';

	// Rollback activity
	$plugin_lang['emajrlbkoperations'] = 'Rollbacks E-Maj';
	$plugin_lang['emajrlbkid'] = 'Id. Rlbk';
	$plugin_lang['emajrlbkstart'] = 'Début rollback';
	$plugin_lang['emajrlbkend'] = 'Fin rollback';
	$plugin_lang['emajduration'] = 'Durée';
	$plugin_lang['emajmarksetat'] = 'Marque posée à';
	$plugin_lang['emajislogged'] = 'Tracé ?';
	$plugin_lang['emajnbsession'] = 'Nb sessions';
	$plugin_lang['emajnbproctable'] = 'Nb tables traitées';
	$plugin_lang['emajnbprocseq'] = 'Nb séquences traitées';
	$plugin_lang['emajcurrentduration'] = 'Durée actuelle';
	$plugin_lang['emajestimremaining'] = 'Restant estimée';
	$plugin_lang['emajpctcompleted'] = '% effectué';
	$plugin_lang['emajinprogressrlbk'] = 'Rollbacks E-Maj en cours';
	$plugin_lang['emajrlbkmonitornotavailable'] = 'Le suivi des rollbacks en cours n\'est pas disponible.';
	$plugin_lang['emajcompletedrlbk'] = 'Rollbacks E-Maj terminés';
	$plugin_lang['emajnbtabletoprocess'] = 'Nb tables à traiter';
	$plugin_lang['emajnbseqtoprocess'] = 'Nb séquences à traiter';
	$plugin_lang['emajnorlbk'] = 'Aucun rollback.';
	$plugin_lang['emajfilterrlbk1'] = 'Afficher les';
	$plugin_lang['emajfilterrlbk2'] = 'plus récents';
	$plugin_lang['emajfilterrlbk3'] = 'terminés depuis moins de';
	$plugin_lang['emajfilterrlbk4'] = 'heures';
	$plugin_lang['emajfilter'] = 'Filtrer';
	$plugin_lang['emajconsolidablerlbk'] = 'Rollbacks E-Maj tracés consolidables';
	$plugin_lang['emajconsolidate'] = 'Consolider';
	$plugin_lang['emajtargetmark'] = 'Marque cible';
	$plugin_lang['emajendrollbackmark'] = 'Marque fin de rollback';
	$plugin_lang['emajnbintermediatemark'] = 'Nb marques intermédiaires';
	$plugin_lang['emajconsolidaterlbk'] = 'Consolider un rollback tracé';
	$plugin_lang['emajconfirmconsolidaterlbk'] = 'Etes-vous sûr de vouloir consolider le rollback terminé par la marque "%s" du groupe de tables "%s" ?';
	$plugin_lang['emajconsolidaterlbkok'] = 'Le rollback terminé par la marque "%s" du groupe de tables "%s" a été consolidé.';
	$plugin_lang['emajconsolidaterlbkerr'] = 'Erreur lors de la consolidation du rollback terminé par la marque "%s" du groupe de tables "%s" !';

	// Group's properties and marks
	$plugin_lang['emajgrouppropertiesmarks'] = 'Propriétés et marques du groupe de tables "%s"';
	$plugin_lang['emajgroupproperties'] = 'Propriétés du groupe de tables "%s"';
	$plugin_lang['emajcontent'] = 'Contenu';
	$plugin_lang['emajgroupmarks'] = 'Marques du groupe de tables "%s"';
	$plugin_lang['emajtimestamp'] = 'Date-Heure';
	$plugin_lang['emajnbupdates'] = 'Nb mises à jour';	
	$plugin_lang['emajcumupdates'] = 'Cumul mises à jour';	
	$plugin_lang['emajsimrlbk'] = 'Simuler Rollback';
	$plugin_lang['emajrlbk'] = 'Rollback';
	$plugin_lang['emajfirstmark'] = 'Première marque';
	$plugin_lang['emajrename'] = 'Renommer';
	$plugin_lang['emajnomark'] = 'Le groupe de tables n\'a pas de marque';
	$plugin_lang['emajprotect'] = 'Protéger';
	$plugin_lang['emajunprotect'] = 'Déprotéger';

	// Statistics
	$plugin_lang['emajshowstat'] = 'Statistiques issues du log E-Maj pour le groupe "%s"';
	$plugin_lang['emajnoupdate'] = 'Aucune mise à jour pour ce groupe de tables';
	$plugin_lang['emajcurrentsituation'] = 'Situation courante';
	$plugin_lang['emajdetailedstat'] = 'Stats détaillées';
	$plugin_lang['emajdetailedlogstatwarning'] = 'Attention, le parcours des tables de log nécessaires à l\'obtention des statistiques détaillées peut être long';
	$plugin_lang['emajlogstatcurrentsituation'] = 'la situation courante';
	$plugin_lang['emajlogstatmark'] = 'la marque "%s"';
	$plugin_lang['emajlogstattittle'] = 'Mises à jour de table entre la marque "%s" et %s pour le groupe de tables "%s"';
	$plugin_lang['emajnosimrlbkduration'] = 'La première marque ne peut pas être utilisée pour un rollback. Aucune durée de rollback ne peut être estimée.';
	$plugin_lang['emajsimrlbkduration'] = 'Le rollback du groupe de tables "%s" à la marque "%s" durerait environ %s.';
	$plugin_lang['emajstatfirstmark'] = 'Première marque';
	$plugin_lang['emajstatfirstmarkdatetime'] = 'Date-Heure première marque';
	$plugin_lang['emajstatlastmark'] = 'Dernière marque';
	$plugin_lang['emajstatlastmarkdatetime'] = 'Date-Heure dernière marque';
	$plugin_lang['emajstatverb'] = 'Verbe SQL';
	$plugin_lang['emajnbinsert'] = 'Nb INSERT';
	$plugin_lang['emajnbupdate'] = 'Nb UPDATE';
	$plugin_lang['emajnbdelete'] = 'Nb DELETE';
	$plugin_lang['emajnbtruncate'] = 'Nb TRUNCATE';
	$plugin_lang['emajnbrole'] = 'Nb rôles';
	$plugin_lang['emajstatrows'] = 'Nb mises à jour';
	$plugin_lang['emajbackgroup'] = 'Revenir au groupe de tables';

	// Group's content
	$plugin_lang['emajgroupcontent'] = 'Contenu du groupe de tables "%s"';
	$plugin_lang['emajemptygroup'] = 'Le groupe de tables "%s" est actuellement vide.';
	$plugin_lang['emajpriority'] = 'Priorité';
	$plugin_lang['emajlogschema'] = 'Schéma de log';
	$plugin_lang['emajlogdattsp'] = 'Tablespace log';
	$plugin_lang['emajlogidxtsp'] = 'Tablespace index log';
	$plugin_lang['emajnamesprefix'] = 'Préfixe nom objets';

	// Group creation
	$plugin_lang['emajcreateagroup'] = 'E-Maj : Créer un groupe de tables';
	$plugin_lang['emajcreateanemptygroup'] = "Création d'un groupe de tables vide";
	$plugin_lang['emajconfirmcreategroup'] = 'Etes-vous sûr de vouloir créer le groupe de tables "%s" ?';
	$plugin_lang['emajinvalidemptygroup'] = 'Erreur, le groupe de table "%s" est déjà créé ou configuré !';
	$plugin_lang['emajcreategroupok'] = 'Le groupe de tables "%s" a été créé.';
	$plugin_lang['emajcreategrouperr'] = 'Erreur lors de la création du groupe de tables "%s" !';

	// Group drop
	$plugin_lang['emajdropagroup'] = 'E-Maj : Supprimer un groupe de tables';
	$plugin_lang['emajconfirmdropgroup'] = 'Etes-vous sûr de vouloir supprimer le groupe de tables "%s" ?';
	$plugin_lang['emajcantdropgroup'] = 'La suppression du groupe de tables "%s" est impossible. Le groupe est démarré.';
	$plugin_lang['emajdropgroupok'] = 'Le groupe de tables "%s" a été supprimé.';
	$plugin_lang['emajdropgrouperr'] = 'Erreur lors de la suppression du groupe de tables "%s" !';

	// Group alter
	$plugin_lang['emajalteraloggingroup'] = 'Le groupe "%s" est actif. Vous pouvez spécifier un nom de marque.';
	$plugin_lang['emajconfirmaltergroup'] = 'Etes-vous sûr de vouloir appliquer les changements de configuration pour le groupe de tables "%s" ?';
	$plugin_lang['emajcantaltergroup'] = 'La modification du groupe "%s" générerait des actions qui ne peuvent être effectuées sur un groupe actif. Arrêtez le groupe avant de le modifier.';
	$plugin_lang['emajaltergroupok'] = 'Les changements de configuration du groupe de tables "%s" ont été appliqués.';
	$plugin_lang['emajaltergrouperr'] = 'Erreur lors de l\'application des changements de configuration pour le groupe de tables "%s" !';

	// Groups alter
	$plugin_lang['emajaltergroups'] = 'E-Maj : Appliquer les changements de configuration';
	$plugin_lang['emajalterallloggingroups'] = 'Les groupes "%s" sont actifs. Vous pouvez spécifier un nom de marque.';
	$plugin_lang['emajconfirmaltergroups'] = 'Etes-vous sûr de vouloir appliquer les changements de configuration pour les groupes de tables "%s" ?';
	$plugin_lang['emajaltergroupsok'] = 'Les changements de configuration des groupes de tables "%s" ont été appliqués.';
	$plugin_lang['emajaltergroupserr'] = 'Erreur lors de l\'application des changements de configuration pour les groupes de tables "%s" !';

	// Group comment
	$plugin_lang['emajcommentagroup'] = 'E-Maj : Enregistrer un commentaire pour un groupe de tables ';
	$plugin_lang['emajcommentgroup'] = 'Entrer, modifier ou supprimer un commentaire pour le groupe de tables "%s"';
	$plugin_lang['emajcommentgroupok'] = 'Le commentaire a été enregistré pour le groupe de tables "%s".';
	$plugin_lang['emajcommentgrouperr'] = 'Erreur lors de l\'enregistrement du commentaire pour le groupe de tables "%s" !';

	// Group protect
	$plugin_lang['emajcantprotectgroup'] = 'La protection du groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$plugin_lang['emajprotectgroupok'] = 'Le groupe de tables "%s" est maintenant protégé contre les rollbacks.';
	$plugin_lang['emajprotectgrouperr'] = 'Erreur lors de la protection du groupe de tables "%s" !';

	// Group unprotect
	$plugin_lang['emajcantunprotectgroup'] = 'La déprotection du groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$plugin_lang['emajunprotectgroupok'] = 'Le groupe de tables "%s" est maintenant déprotégé.';
	$plugin_lang['emajunprotectgrouperr'] = 'Erreur lors de la deprotection du groupe de tables "%s" !';

	// Group start
	$plugin_lang['emajstartagroup'] = 'E-Maj : Démarrer un groupe de tables';
	$plugin_lang['emajconfirmstartgroup'] = 'Démarrage du groupe de tables "%s"';
	$plugin_lang['emajinitmark'] = 'Marque initiale';
	$plugin_lang['emajoldlogsdeletion'] = 'Suppression des anciens logs';
	$plugin_lang['emajcantstartgroup'] = 'Le démarrage du groupe de tables "%s" est impossible. Le groupe est déjà démarré.';
	$plugin_lang['emajstartgroupok'] = 'Le groupe de tables "%s" est démarré avec la marque "%s".';
	$plugin_lang['emajstartgrouperr'] = 'Erreur lors du démarrage du groupe de tables "%s" !';	

	// Groups start
	$plugin_lang['emajstartgroups'] = 'E-Maj : Démarrer des groupes de tables';
	$plugin_lang['emajconfirmstartgroups'] = 'Démarrage des groupes de tables "%s"';
	$plugin_lang['emajcantstartgroups'] = 'Le démarrage des groupes de tables "%s" est impossible. Le groupe "%s" est déjà démarré.';
	$plugin_lang['emajstartgroupsok'] = 'Les groupes de tables "%s" ont été démarrés avec la marque "%s".';
	$plugin_lang['emajstartgroupserr'] = 'Erreur lors du démarrage des groupes de tables "%s" !';

	// Group stop
	$plugin_lang['emajstopagroup'] = 'E-Maj : Arrêter un groupe de tables ';
	$plugin_lang['emajconfirmstopgroup'] = 'Arrêt du groupe de tables "%s"';
	$plugin_lang['emajstopmark'] = 'Marque finale';
	$plugin_lang['emajforcestop'] = 'Forcer l\'arrêt (en cas de problème seulement)';
	$plugin_lang['emajcantstopgroup'] = 'L\'arrêt du groupe de tables "%s" est impossible. Le groupe est déjà arrêté.';
	$plugin_lang['emajstopgroupok'] = 'Le groupe de tables "%s" a été arrêté.';
	$plugin_lang['emajstopgrouperr'] = 'Erreur lors de l\'arrêt du groupe de tables "%s" !';

	// Groups stop
	$plugin_lang['emajstopgroups'] = 'E-Maj : Arrêter des groupes de tables';
	$plugin_lang['emajconfirmstopgroups'] = 'Arrêt des groupes de tables "%s"';
	$plugin_lang['emajcantstopgroups'] = 'L\'arrêt des groupes de tables "%s" est impossible. Le groupe "%s" est déjà arrêté.';
	$plugin_lang['emajstopgroupsok'] = 'Les groupes de tables "%s" ont été arrêtés.';
	$plugin_lang['emajstopgroupserr'] = 'Erreur lors de l\'arrêt des groupes de tables "%s" !';

	// Group reset
	$plugin_lang['emajresetagroup'] = 'E-Maj : Réinitialiser un groupe de tables';
	$plugin_lang['emajconfirmresetgroup'] = 'Etes-vous sûr de vouloir réinitialiser le groupe de tables "%s" ?';
	$plugin_lang['emajcantresetgroup'] = 'La réinitialisation du groupe de tables "%s" est impossible. Le groupe est démarré.';
	$plugin_lang['emajresetgroupok'] = 'Le groupe de tables "%s" est réinitialisé.';
	$plugin_lang['emajresetgrouperr'] = 'Erreur lors de la réinitialisation du groupe de tables "%s" !';

	// Set Mark for one or several groups
	$plugin_lang['emajsetamark'] = 'E-Maj : Poser une marque';
	$plugin_lang['emajconfirmsetmarkgroup'] = 'Pose d\'une marque pour le(s) groupe(s) de tables "%s" :';
	$plugin_lang['emajcantsetmarkgroup'] = 'La pose d\'une marque pour le groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$plugin_lang['emajinvalidmark'] = 'La marque saisie (%s) est invalide.';
	$plugin_lang['emajsetmarkgroupok'] = 'La marque "%s" est posée pour le(s) groupe(s) de tables "%s".';
	$plugin_lang['emajsetmarkgrouperr'] = 'Erreur lors de la pose de la marque "%s" pour le(s) groupe(s) de tables "%s" !';
	$plugin_lang['emajcantsetmarkgroups'] = 'La pose d\'une marque pour les groupes de tables "%s" est impossible. Le groupe "%s" est arrêté.';

	// Protect mark
	$plugin_lang['emajcantprotectmarkgroup'] = 'La protection d\'une marque pour le groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$plugin_lang['emajprotectmarkgroupok'] = 'La marque "%s" du groupe de tables "%s" est maintenant protégé contre les rollbacks.';
	$plugin_lang['emajprotectmarkgrouperr'] = 'Erreur lors de la protection de la marque "%s" du groupe de tables "%s" !';

	// Unprotect mark
	$plugin_lang['emajcantunprotectmarkgroup'] = 'La déprotection d\'une marque pour le groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$plugin_lang['emajunprotectmarkgroupok'] = 'La marque "%s" du groupe de tables "%s" est maintenant déprotégé.';
	$plugin_lang['emajunprotectmarkgrouperr'] = 'Erreur lors de la déprotection de la marque "%s" du groupe de tables "%s" !';

	// Comment mark
	$plugin_lang['emajcommentamark'] = 'E-Maj : Enregistrer un commentaire pour une marque';
	$plugin_lang['emajcommentmark'] = 'Entrer, modifier ou supprimer le commentaire pour la marque "%s" du groupe de tables "%s".';
	$plugin_lang['emajcommentmarkok'] = 'Le commentaire a été enregistré pour la marque "%s" du groupe de tables "%s".';
	$plugin_lang['emajcommentmarkerr'] = 'Erreur lors de l\'enregistrement du commentaire pour la marque "%s" du groupe de tables "%s" !';

	// Group rollback
	$plugin_lang['emajrlbkagroup'] = 'E-Maj : Rollbacker un groupe de tables';
	$plugin_lang['emajconfirmrlbkgroup'] = 'Rollback du groupe de tables "%s" à la marque "%s"';
	$plugin_lang['emajselectmarkgroup'] = 'Rollback du groupe de tables "%s" à la marque : ';
	$plugin_lang['emajrlbkthenmonitor'] = 'Rollback et suivi';
	$plugin_lang['emajcantrlbkidlegroup'] = 'Le rollback du groupe de tables "%s" est impossible. Le groupe est arrêté.';
	$plugin_lang['emajcantrlbkprotgroup'] = 'Le rollback du groupe de tables "%s" est impossible. Le groupe est protégé.';
	$plugin_lang['emajcantrlbkinvalidmarkgroup'] = 'Le rollback du groupe de tables "%s" est impossible. La marque "%s" n\'est pas valide.';
	$plugin_lang['emajreachaltergroup'] = 'Le rollback du groupe de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification du groupe. Veuillez confirmer le rollback.';
	$plugin_lang['emajautorolledback'] = 'Annulé automatiquement ?';
	$plugin_lang['emajgroupisprotected'] = 'Le groupe "%s" est protégé.';
	$plugin_lang['emajinvalidrlbkmark'] = 'La marque "%s" n\'est plus valide.';
	$plugin_lang['emajrlbkgroupok'] = 'Le rollback du groupe de tables "%s" à la marque "%s" est effectué.';
	$plugin_lang['emajrlbkgrouperr'] = 'Erreur lors du rollback du groupe de tables "%s" à la marque "%s" !';
	$plugin_lang['emajbadpsqlpath'] = 'Rollback asynchrone impossible : le chemin de la commande psql configurée (%s) est invalide.';
	$plugin_lang['emajbadtempdir'] = 'Rollback asynchrone impossible : le répertoire temporaire configuré (%s) est invalide.';
	$plugin_lang['emajasyncrlbkstarted'] = 'Rollback démarré (id = %s).';

	// Groups rollback
	$plugin_lang['emajrlbkgroups'] = 'E-Maj : Rollbacker des groupes de tables';
	$plugin_lang['emajselectmarkgroups'] = 'Rollback des groupes de tables "%s" à la marque : ';
	$plugin_lang['emajcantrlbkidlegroups'] = 'Le rollback des groupes de tables "%s" est impossible. Le groupe "%s" est arrêté.';
	$plugin_lang['emajcantrlbkprotgroups'] = 'Le rollback des groupes de tables "%s" est impossible. Les groupes "%s" sont protégés.';
	$plugin_lang['emajnomarkgroups'] = 'Aucune marque commune aux groupes de tables "%s" ne peut être utilisée pour un rollback.';
	$plugin_lang['emajcantrlbkinvalidmarkgroups'] = 'Le rollback des groupes de tables "%s" est impossible. La marque "%s" n\'est pas valide.';
	$plugin_lang['emajreachaltergroups'] = 'Le rollback des groupes de tables "%s" à la marque "%s" remonterait à un point dans le temps antérieur à des opérations de modification de groupes. Veuillez confirmer le rollback.';
	$plugin_lang['emajrlbkgroupsok'] = 'Le rollback des groupes de tables "%s" à la marque "%s" est effectué.';
	$plugin_lang['emajrlbkgroupserr'] = 'Erreur lors du rollback des groupes de tables "%s" à la marque "%s" !';

	// Elementary alter group actions previously executed, reported at rollback time 
	$plugin_lang['emajalteredremovetbl'] = 'La table "%s.%s" a été supprimée du groupe de tables %s';
	$plugin_lang['emajalteredremoveseq'] = 'La séquence "%s.%s" a été supprimée du groupe de tables %s';
	$plugin_lang['emajalteredrepairtbl'] = 'Les objets E-Maj pour la table "%s.%s" ont été reparés';
	$plugin_lang['emajalteredrepairseq'] = 'Les objets E-Maj pour la séquence "%s.%s" ont été reparés';
	$plugin_lang['emajalteredchangetbllogschema'] = 'Le schéma de log E-Maj pour la table "%s.%s" a été modifié';
	$plugin_lang['emajalteredchangetblnamesprefix'] = 'Le préfixe des noms E-Maj pour la table "%s.%s" a été modifié';
	$plugin_lang['emajalteredchangetbllogdatatsp'] = 'Le tablespace pour le log de la table "%s.%s" a été modifié';
	$plugin_lang['emajalteredchangetbllogindextsp'] = 'Le tablespace pour les index de log de la table "%s.%s" a été modifié';
	$plugin_lang['emajalteredassignrel'] = 'La table ou séquence "%s.%s" a été déplacée du groupe de tables "%s" au groupe de tables "%s"';
	$plugin_lang['emajalteredchangerelpriority'] = 'La priorité E-Maj pour la table "%s.%s" a été modifiée';
	$plugin_lang['emajalteredaddtbl'] = 'La table "%s.%s" a été ajoutée au groupe de tables "%s"';
	$plugin_lang['emajalteredaddseq'] = 'La séquence "%s.%s" a été ajoutée au groupe de tables "%s"';

	// Mark renaming
	$plugin_lang['emajrenameamark'] = 'E-Maj : Renommer une marque';
	$plugin_lang['emajconfirmrenamemark'] = 'Renomage de la marque "%s" du groupe de tables "%s"';
	$plugin_lang['emajnewnamemark'] = 'Nouveau nom';
	$plugin_lang['emajrenamemarkok'] = 'La marque "%s" du groupe de tables "%s" a été renommée en "%s".';
	$plugin_lang['emajrenamemarkerr'] = 'Erreur lors du renommage de la marque "%s" du groupe de tables "%s" en "%s" !';

	// Mark deletion
	$plugin_lang['emajdelamark'] = 'E-Maj : Effacer une marque';
	$plugin_lang['emajconfirmdelmark'] = 'Etes-vous sûr de vouloir effacer la marque "%s" pour le groupe de tables "%s" ?';
	$plugin_lang['emajdelmarkok'] = 'La marque "%s" a été effacée pour le groupe de tables "%s".';
	$plugin_lang['emajdelmarkerr'] = 'Erreur lors de l\'effacement de la marque "%s" pour le groupe de tables "%s" !';

	// Marks before mark deletion
	$plugin_lang['emajdelmarks'] = 'E-Maj : Supprimer des marques';
	$plugin_lang['emajconfirmdelmarks'] = 'Etes-vous sûr de vouloir supprimer toutes les marques et log antérieurs à la marque "%s" pour le groupe de tables "%s" ?';
	$plugin_lang['emajdelmarksok'] = 'Les (%s) marques antérieures à la marque "%s" ont été supprimées pour le groupe de tables "%s".';
	$plugin_lang['emajdelmarkserr'] = 'Erreur lors de la suppression des marques antérieures à la marque "%s" pour le groupe de tables "%s" !';
?>
