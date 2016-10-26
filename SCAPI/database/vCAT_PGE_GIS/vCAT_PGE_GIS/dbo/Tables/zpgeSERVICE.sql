CREATE TABLE [dbo].[zpgeSERVICE] (
    [OBJECTID]                      INT              IDENTITY (1, 1) NOT NULL,
    [ENABLED]                       SMALLINT         NULL,
    [GLOBALID]                      UNIQUEIDENTIFIER NOT NULL,
    [SOURCEACCURACY]                NVARCHAR (14)    NULL,
    [CONSTRUCTIONSTATUS]            NVARCHAR (10)    NULL,
    [INSTALLEDJOBORDER]             NVARCHAR (40)    NULL,
    [INSTALLEDCOMPLETIONDATE]       DATETIME2 (7)    NULL,
    [CONVERSIONID]                  INT              NULL,
    [CONVERSIONWORKPACKAGE]         NVARCHAR (40)    NULL,
    [NOMINALDIAMETER]               NVARCHAR (10)    NULL,
    [COATINGTYPE]                   NVARCHAR (5)     NULL,
    [LOCATINGWIREINDICATOR]         NVARCHAR (3)     NULL,
    [PLASTICTYPE]                   NVARCHAR (10)    NULL,
    [MEASUREDLENGTH]                NUMERIC (38, 8)  NULL,
    [LENGTHSOURCE]                  NVARCHAR (5)     NULL,
    [MANUFACTURER]                  NVARCHAR (20)    NULL,
    [INSTALLATIONMETHOD]            NVARCHAR (20)    NULL,
    [GASTRACEWEIGHT]                INT              NULL,
    [BONDEDINDICATOR]               NVARCHAR (3)     NULL,
    [PRESSURECLASSIFICATION]        NVARCHAR (3)     NULL,
    [FIRSTCONDUITPIPESIZE]          NVARCHAR (4)     NULL,
    [FIRSTCONDUITPIPEMATERIAL]      NVARCHAR (3)     NULL,
    [SECONDCONDUITPIPESIZE]         NVARCHAR (4)     NULL,
    [SECONDCONDUITPIPEMATERIAL]     NVARCHAR (3)     NULL,
    [JOINTTRENCHINDICATOR]          NVARCHAR (3)     NULL,
    [LINEDINDICATOR]                NVARCHAR (3)     NULL,
    [MLXAGREEMENT]                  NVARCHAR (20)    NULL,
    [MLXDATE]                       DATETIME2 (7)    NULL,
    [CROSSINGTYPE]                  NVARCHAR (9)     NULL,
    [MATERIAL]                      INT              NULL,
    [SERVICEORDERNUMBER]            NVARCHAR (255)   NULL,
    [SERVICETYPE]                   NVARCHAR (20)    NULL,
    [CRITICALINDICATOR]             NVARCHAR (3)     NULL,
    [SPECIALFACILITIESDATE]         DATETIME2 (7)    NULL,
    [NONLOCATABLESTUB]              NVARCHAR (3)     NULL,
    [TENPCTERINDICATOR]             NVARCHAR (3)     NULL,
    [TENPCTERLASTREADDATE]          DATETIME2 (7)    NULL,
    [CORRODIBLERISERINDICATOR]      NVARCHAR (3)     NULL,
    [PGEREMOTEUNITNUMBER]           NVARCHAR (50)    NULL,
    [REMOTEINSTALLATIONDATE]        DATETIME2 (7)    NULL,
    [REMOTEMODEL]                   NVARCHAR (50)    NULL,
    [REMOTEPIPETOSOILSITEINDICATOR] NVARCHAR (3)     NULL,
    [INSERTIND]                     NVARCHAR (3)     NULL,
    [SPECIALFACILITYJOBORDER]       NVARCHAR (30)    NULL,
    [PHYSICALPLAT]                  NVARCHAR (10)    NULL,
    [MAPSCALE]                      INT              NULL,
    [DIVISION]                      INT              NULL,
    [COUNTY]                        NVARCHAR (15)    NULL,
    [ZIPCODE]                       NVARCHAR (15)    NULL,
    [CITY]                          NVARCHAR (50)    NULL,
    [PUBLICASSEMBLYINDICATOR]       NVARCHAR (3)     NULL,
    [CREATEUSER]                    NVARCHAR (30)    NULL,
    [CREATEDATE]                    DATETIME2 (7)    NULL,
    [UPDATEUSER]                    NVARCHAR (30)    NULL,
    [UPDATEDATE]                    DATETIME2 (7)    NULL,
    [EQUIPMENTID]                   NVARCHAR (50)    NULL,
    [SAPOBJECTID]                   NVARCHAR (50)    NULL,
    [EMERGENCYZONENAME1]            NVARCHAR (15)    NULL,
    [EMERGENCYZONENAME2]            NVARCHAR (15)    NULL,
    [EMERGENCYZONENAME3]            NVARCHAR (15)    NULL,
    [EMERGENCYZONENAME4]            NVARCHAR (15)    NULL,
    [HOUSEADDRESS]                  NVARCHAR (255)   NULL,
    [STREETNAME]                    NVARCHAR (255)   NULL,
    [WORKCENTER]                    NVARCHAR (10)    NULL,
    [LOCATIONDESCRIPTION]           NVARCHAR (255)   NULL,
    [APPLICANTINSTALLEXPDATE]       DATETIME2 (7)    NULL,
    [APPLICANTTRENCHEXPDATE]        DATETIME2 (7)    NULL,
    [RECORDEDPLAT]                  NVARCHAR (10)    NULL,
    [PHYSICALWALLMAP]               NVARCHAR (10)    NULL,
    [RECORDEDBLOCK]                 NVARCHAR (10)    NULL,
    [SPECIALFACILITIES]             NVARCHAR (3)     NULL,
    [EMMARKERINSTALLED]             NVARCHAR (3)     NULL,
    [RECORDEDWALLMAP]               NVARCHAR (10)    NULL,
    [HARDTOLOCATEINDICATOR]         NVARCHAR (3)     NULL,
    [THERMALBILLINGAREANAME]        NVARCHAR (100)   NULL,
    [SYMBOLSCALE]                   INT              NULL,
    [CAPNUMBER]                     NVARCHAR (30)    NULL,
    [COGENSERVICEINDICATOR]         NVARCHAR (3)     NULL,
    [CPANAME]                       NVARCHAR (50)    NULL,
    [CPSYSTEMSTATUS]                INT              NULL,
    [CPTYPE]                        NVARCHAR (25)    NULL,
    [FOREMANLANID]                  NVARCHAR (10)    NULL,
    [GASPRESSURESYSTEMNAME]         NVARCHAR (50)    NULL,
    [INSULATEDINDICATOR]            NVARCHAR (3)     NULL,
    [OVERHEADSERVICEINDIACOR]       NVARCHAR (3)     NULL,
    [PIPETYPE]                      NVARCHAR (5)     NULL,
    [PLANNINGMODELNAME]             NVARCHAR (50)    NULL,
    [SERVICEID]                     INT              NULL,
    [STATUS]                        NVARCHAR (3)     NULL,
    [SHAPE]                         [sys].[geometry] NULL,
    [OPERATIONALDATE]               DATETIME2 (7)    NULL,
    [RWNUMBER]                      INT              NULL,
    [ASBESTOSDETECTEDIND]           NVARCHAR (3)     NULL,
    [ASBESTOSTESTRPRTDAT]           DATETIME2 (7)    NULL,
    [ASBESTOSTESTRPRTLAB]           NVARCHAR (100)   NULL,
    [ASBESTOSTESTRPRTNUM]           NVARCHAR (50)    NULL,
    [SAPMAINTENANCESTATUS]          NVARCHAR (10)    NULL,
    [INTEGRITYMANAGEMENTAREA]       NVARCHAR (20)    NULL,
    [APPLICANTINSTALLNAME]          NVARCHAR (100)   NULL,
    [DATEOFMANUFACTURE]             DATETIME2 (7)    NULL,
    PRIMARY KEY CLUSTERED ([OBJECTID] ASC)
);


GO
CREATE NONCLUSTERED INDEX [I0SERVICEID]
    ON [dbo].[zpgeSERVICE]([SERVICEID] ASC);


GO
CREATE NONCLUSTERED INDEX [I0MATERIAL]
    ON [dbo].[zpgeSERVICE]([MATERIAL] ASC);


GO
CREATE NONCLUSTERED INDEX [G0GASPRESSURESYS]
    ON [dbo].[zpgeSERVICE]([GASPRESSURESYSTEMNAME] ASC);


GO
CREATE NONCLUSTERED INDEX [I0OBJECTID4]
    ON [dbo].[zpgeSERVICE]([OBJECTID] ASC, [CONSTRUCTIONSTATUS] ASC);


GO
CREATE NONCLUSTERED INDEX [I0CPANAME]
    ON [dbo].[zpgeSERVICE]([CPANAME] ASC);


GO
CREATE SPATIAL INDEX [FDO_SHAPE]
    ON [dbo].[zpgeSERVICE] ([SHAPE])
    USING GEOMETRY_GRID
    WITH  (
            BOUNDING_BOX = (XMAX = 20081600, XMIN = -16800800, YMAX = 32802000, YMIN = -32802000),
            GRIDS = (LEVEL_1 = MEDIUM, LEVEL_2 = MEDIUM, LEVEL_3 = MEDIUM, LEVEL_4 = MEDIUM)
          );

