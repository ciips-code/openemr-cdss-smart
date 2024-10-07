# OpenEMR Custom FHIR Integration Module

## Overview
The custom module for OpenEMR is designed to enable seamless integration with an external FHIR server, allowing for the transformation and synchronization of clinical records. This module focuses on converting OpenEMR data into FHIR-compliant resources and provides functionality to interact with FHIR's `$apply` operation on PlanDefinitions.

## Key Features

### 1. FHIR Connectivity
- The module establishes a connection with an external FHIR server using RESTful APIs. This allows OpenEMR to send and receive FHIR resources, ensuring interoperability with other systems that follow the FHIR R4 standard.

### 2. Data Transformation
- OpenEMR records such as patient demographics, clinical conditions, and procedures are transformed into corresponding FHIR resources. The primary resource mappings include:
    - **Patient**: Maps OpenEMR patient data to FHIR's `Patient` resource.
    - **Condition**: Maps clinical diagnoses and conditions from OpenEMR to the FHIR `Condition` resource.
    - **Procedure**: Converts OpenEMR procedures into the FHIR `Procedure` resource.
- The transformation is handled through a set of custom data mapping rules, ensuring compliance with the FHIR schema.

### 3. FHIR Resource Upload
- After transformation, the FHIR resources are automatically uploaded to the connected FHIR server.
- The module supports creating and updating resources on the FHIR server through HTTP `PUT` method.

### 4. `$apply` Operation
- The module is capable of executing the FHIR `$apply` operation, specifically targeting PlanDefinitions. Users can select a pre-configured set of PlanDefinitions from within the OpenEMR interface.
- The `$r5.apply` operation allows the system to execute clinical protocols, care plans, and workflows as defined by the PlanDefinition resources on the FHIR server. This operation ensures that clinical pathways and decision support workflows are applied based on predefined rules.

## Installation Instructions

To install and configure the OpenEMR Custom FHIR Integration Module, follow these steps:

1. **Clone the Repository**:
   Clone the module repository into the OpenEMR custom module directory:
   ```bash
   git clone https://github.com/ciips-code/openemr-cdss-smart.git /path/to/openemr/interface/modules/custom_modules/openemr-cdss-smart
    ```
2. **Login to OpenEMR**
    - Open your web browser and navigate to your OpenEMR instance.
    - Login with your credentials.

3. **Install and Enable the Module**
    - Once logged in, go to **Administration** > **Manage Modules**.
    - Locate the newly cloned module in the module list and click **Install**.
    - After installation, enable the module by selecting **Enable**.

4. **Configure Module Parameters**
    - Navigate to **Administration** > **Globals** > **Modules**.
    - Find the FHIR Integration module in the list and configure the following parameters:
        - FHIR server URL
        - Authentication credentials (OAuth 2.0, API keys, etc.)
        - Other configuration options such as resource mapping and PlanDefinition settings.

---
