# Referential Integrity

This repository contains the code of a module for Omeka S (version 4.x.x) enabling referential integrity, to make the behaviour of Omeka S closer to relational database systems with primary and foreign keys.
Its aim is to prevent deletion of objects (items) that are the target (or parent) of a relationship from others object (child objects or "linked resources").

A protected relationship is defined by : 
1. the template definign the structure of the source item ("foreign key side") 
2. the property having  relations to the child objects within this source template ("foreign key side") 
3. the target template associated to the other end of the relatonship ("primary key side")

So the model has to be defined by mapping properties within a template (not a class)

## Requirements
PHP >= 8.0 (developped with PHP 8.4)
Omeka S >= ^4.0.0 (not tested on older versions)


## Principle

Constraints are defined in a single **referential_integrity** table
1. When an object is being deleted, a listener in Module.php intercepts the **api.delete.pre** event and access the item to be deleted.
2. It checks whether the parent template of the object is defined as a target template in the table
3. If yes, for each matching constraints, the module retrieves the corresponding  source template and source_property
4. It counts in the **value** table the objects corresponding to the
   - source propery
   - source template
   - the value_resource_id column value matching the id of the submitted object
5. If one or more records are found, it raises an exception written to the log. The error message displays :
   - the title of the objct
   - the number of corresponding objet per source template
  
## Caveats
The module works for batch delete but doesn't show the exception warning when deletion is prevented. It deletes the object without linking, but keeps those that are associated to child records.
The database model forecast a cascade delete option, not yet implemented.
It has only been tested with constraints defined by admin.
