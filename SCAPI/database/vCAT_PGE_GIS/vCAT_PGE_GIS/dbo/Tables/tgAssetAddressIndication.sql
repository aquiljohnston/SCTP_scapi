﻿CREATE TABLE [dbo].[tgAssetAddressIndication] (
    [AssetAddressIndicationsID]              INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [AssetAddressIndicationUID]              VARCHAR (100)      NULL,
    [AssetAddressUID]                        VARCHAR (100)      NULL,
    [InspectionRequestUID]                   VARCHAR (100)      NULL,
    [MapGridUID]                             VARCHAR (100)      NULL,
    [MasterLeakLogUID]                       VARCHAR (100)      NULL,
    [ProjectID]                              INT                NULL,
    [SourceID]                               VARCHAR (100)      NULL,
    [CreatedUserUID]                         VARCHAR (100)      NOT NULL,
    [ModifiedUserUID]                        VARCHAR (100)      CONSTRAINT [DF_tgAssetAddressIndication_ModifiedUserUID] DEFAULT ('') NOT NULL,
    [SrcDTLT]                                DATETIME           NOT NULL,
    [SrvDTLT]                                DATETIME           CONSTRAINT [DF_g_AssetIndications_SrvDTLT] DEFAULT (getdate()) NULL,
    [SrvDTLTOffset]                          DATETIMEOFFSET (7) CONSTRAINT [DF_g_AssetIndications_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [SrcOpenDTLT]                            DATETIME           NULL,
    [SrcClosedDTLT]                          DATETIME           NULL,
    [GPSType]                                VARCHAR (100)      NULL,
    [GPSSentence]                            VARCHAR (400)      NULL,
    [Latitude]                               FLOAT (53)         NULL,
    [Longitude]                              FLOAT (53)         NULL,
    [SHAPE]                                  [sys].[geography]  NULL,
    [Comments]                               VARCHAR (2000)     NULL,
    [RevisionComments]                       VARCHAR (500)      NULL,
    [Revision]                               INT                CONSTRAINT [DF_g_AssetIndications_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]                             BIT                CONSTRAINT [DF_tgAssetAddressIndication_ActiveFlag] DEFAULT ((1)) NULL,
    [StatusType]                             VARCHAR (50)       CONSTRAINT [DF_g_AssetIndications_StatusType] DEFAULT ('In Progress') NULL,
    [ManualMapPlat]                          VARCHAR (200)      NULL,
    [PipelineType]                           VARCHAR (200)      NULL,
    [SurveyType]                             VARCHAR (200)      NULL,
    [Map]                                    VARCHAR (200)      NULL,
    [Plat]                                   VARCHAR (200)      NULL,
    [RecordedMap]                            VARCHAR (200)      NULL,
    [RecordedPlat]                           VARCHAR (200)      NULL,
    [RecordedBlock]                          VARCHAR (200)      NULL,
    [LandmarkType]                           VARCHAR (200)      NULL,
    [Route]                                  VARCHAR (200)      NULL,
    [Line]                                   VARCHAR (200)      NULL,
    [HouseNoNAFlag]                          BIT                NULL,
    [HouseNo]                                VARCHAR (100)      NULL,
    [Street1]                                VARCHAR (100)      NULL,
    [City]                                   VARCHAR (50)       NULL,
    [DescriptionReadingLocation]             VARCHAR (100)      NULL,
    [County]                                 VARCHAR (255)      NULL,
    [CountyCode]                             VARCHAR (255)      NULL,
    [FacilityType]                           VARCHAR (200)      NULL,
    [LocationType]                           VARCHAR (200)      NULL,
    [InitialLeakSourceType]                  VARCHAR (200)      NULL,
    [ReportedByType]                         VARCHAR (200)      NULL,
    [LeakNo]                                 VARCHAR (100)      NULL,
    [SAPNo]                                  VARCHAR (200)      NULL,
    [PavedType]                              VARCHAR (200)      NULL,
    [SORLType]                               VARCHAR (200)      NULL,
    [SORLOther]                              VARCHAR (200)      NULL,
    [Within5FeetOfBuildingType]              VARCHAR (200)      NULL,
    [SuspectedCopperType]                    VARCHAR (255)      NULL,
    [EquipmentFoundByUID]                    VARCHAR (100)      NULL,
    [FoundBy]                                VARCHAR (100)      NULL,
    [FoundBySerialNumber]                    VARCHAR (50)       NULL,
    [InstrumentTypeGradeByType]              VARCHAR (200)      NULL,
    [EquipmentGradeByUID]                    VARCHAR (100)      NULL,
    [GradeBy]                                VARCHAR (100)      NULL,
    [GradeBySerialNumber]                    VARCHAR (50)       NULL,
    [ReadingGrade]                           FLOAT (53)         NULL,
    [GradeType]                              VARCHAR (200)      NULL,
    [InfoCodesType]                          VARCHAR (200)      NULL,
    [PotentialHCAType]                       VARCHAR (200)      NULL,
    [Grade2PlusRequested]                    DATE               NULL,
    [TwoPercentOrLessSuspectCopperFlag]      BIT                NULL,
    [LeakDownGradedFlag]                     VARCHAR (255)      NULL,
    [HCAConstructionSupervisorUserUID]       VARCHAR (100)      NULL,
    [HCADistributionPlanningEngineerUserUID] VARCHAR (100)      NULL,
    [HCAPipelineEngineerUserUID]             VARCHAR (100)      NULL,
    [Photo1]                                 VARCHAR (250)      NULL,
    [Photo2]                                 VARCHAR (250)      NULL,
    [Photo3]                                 VARCHAR (250)      NULL,
    [OptionalData1]                          VARCHAR (250)      NULL,
    [OptionalData2]                          VARCHAR (250)      NULL,
    [OptionalData3]                          VARCHAR (250)      NULL,
    [OptionalData4]                          VARCHAR (250)      NULL,
    [OptionalData5]                          VARCHAR (250)      NULL,
    [OptionalData6]                          VARCHAR (250)      NULL,
    [OptionalData7]                          VARCHAR (250)      NULL,
    [OptionalData8]                          VARCHAR (250)      NULL,
    [OptionalData9]                          VARCHAR (250)      NULL,
    [OptionalData10]                         VARCHAR (250)      NULL,
    [OptionalData11]                         VARCHAR (250)      NULL,
    [OptionalData12]                         VARCHAR (250)      NULL,
    [ApprovedFlag]                           BIT                NULL,
    [ApprovedByUserUID]                      VARCHAR (100)      NULL,
    [ApprovedDTLT]                           DATETIME           NULL,
    [SubmittedFlag]                          BIT                NULL,
    [SubmittedStatusType]                    VARCHAR (200)      NULL,
    [SubmittedUserUID]                       VARCHAR (100)      NULL,
    [SubmittedDTLT]                          DATETIME           NULL,
    [ResponseStatusType]                     VARCHAR (200)      NULL,
    [ResponseComments]                       VARCHAR (500)      NULL,
    [ResponceErrorComments]                  VARCHAR (500)      NULL,
    [ResponseDTLT]                           DATETIME           NULL,
    [CompletedFlag]                          BIT                CONSTRAINT [DF_tgAssetAddressIndication_CompletedFlag] DEFAULT ((0)) NOT NULL,
    [CompletedDTLT]                          DATETIME           NULL,
    [AboveBelowGroundType]                   VARCHAR (200)      NULL,
    [FoundDateTime]                          DATETIME           NULL,
    [GPSSource]                              VARCHAR (20)       NULL,
    [GPSTime]                                VARCHAR (10)       NULL,
    [FixQuality]                             INT                NULL,
    [NumberOfSatellites]                     INT                NULL,
    [HDOP]                                   FLOAT (53)         NULL,
    [AltitudemetersAboveMeanSeaLevel]        FLOAT (53)         NULL,
    [HeightOfGeoid]                          FLOAT (53)         NULL,
    [TimeSecondsSinceLastDGPS]               FLOAT (53)         NULL,
    [ChecksumData]                           VARCHAR (10)       NULL,
    [Bearing]                                FLOAT (53)         NULL,
    [Speed]                                  FLOAT (53)         NULL,
    [GPSStatus]                              VARCHAR (20)       NULL,
    [NumberOfGPSAttempts]                    INT                NULL,
    [ActivityUID]                            VARCHAR (100)      NULL,
    [AssetInspectionUID]                     VARCHAR (100)      NULL,
    [MapPlatLeakNumber]                      INT                NULL,
    [LockedFlag]                             BIT                CONSTRAINT [DF_tgAssetAddressIndication_LockedFlag] DEFAULT ((1)) NULL,
    [SAPComments]                            VARCHAR (500)      NULL,
    CONSTRAINT [PK_g_AssetIndications] PRIMARY KEY CLUSTERED ([AssetAddressIndicationsID] ASC)
);

