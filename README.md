# coreBOSIERelations
Import/Export Relations Extension

This extension permits exporting information about **many to many relations between records** from one coreBOS install to another.

This extension must be installed in both coreBOS applications. On one, you will **export** the relations, and on the other, you will **import** them.

## Export

The export process will generate an XML file with all the necessary information to recover the relations in the destination installation. This information is fundamentally the autonumber identifier field and the internal crmid of the record.

The way the records will be identified in the importing system will be either by the autonumber field or the CRMID which was exported. This means that **you MUST** import this information in the destination system **first**.

## Import

The import process will read the XML file, search for the records using the autonumber or CRMID identifier from the origin installation, and establish the relation between the two modules.

## Process

The full process goes more or less like this:

  * Export the records of ModuleA from the origin system. Make sure you have the autonumber identifier field. Optionally the CRMID.
  * Install ModuleA in the destination system and add a custom field called PreviousIDField (or something like that)
  * Import the records into ModuleA on the destination system making sure that you save the autonumber field into the custom field PreviousIDField
  * You can do the same with the CRMID, or put the CRMID in the PreviousIDField instead of the autonumber identifier. That depends on how you want to identify the records that are in the import file.
  * Repeat the process above for ModuleB
  * Using coreBOSIERelations in the origin coreBOS,
    * Export the relation information between ModuleA and ModuleB
  * Using coreBOSIERelations in the destination coreBOS,
    * Select the XML file
    * Select the PreviousIDField where you imported the autonumber field on ModuleA
    * Select the PreviousIDField where you imported the autonumber field on ModuleB
    * Launch the import process
  * Verify the import process
  * Optionally delete or hide the PreviousIDField custom field

## Notes

  * **One to many** relations are already supported natively, you just have to use the supported syntax and import in the correct order of dependencies of your modules.
  * Only vtlib standard (_crmentityrel) many to many and document attachments relations are currently supported. Donations and/or pull requests are welcome to support custom many to many relation tables.

## Use cases

Some uses of the extension are:

 - Share information between two or more coreBOS installations
 - Migrate information from a test install to a production system after validation
 - Mass relate many records
 - Copy relations from one record to another

