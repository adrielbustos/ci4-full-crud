<?php

namespace App\Models\CICrud;

interface ErrorCodes {

    // INDETERMINATE
    const INDETERMINATEERROR = 0000;

    // SQL ERRORS
    const UNIQUEERROR = 1013;
    const FKCONSTRAINERROR = 1014;
    const VALUENOTNULL = 1015;
    const FKDELETEERROR = 1016;

    // FIREBACK ERRORS
    const METHODNOTEXIST = 102;

    // ADVANCED FILTERS ERRORS
    const QUERYPARAMSERROR = 404;

}
