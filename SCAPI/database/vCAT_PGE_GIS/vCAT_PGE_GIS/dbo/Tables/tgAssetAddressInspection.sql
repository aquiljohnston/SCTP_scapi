﻿CREATE TABLE [dbo].[tgAssetAddressInspection] (
    [AssetAddressInspectionID]        INT                IDENTITY (1, 1) NOT NULL,
    [AssetAddressInspectionUID]       VARCHAR (100)      NULL,
    [AssetAddressUID]                 VARCHAR (100)      NULL,
    [AssetInspectionUID]              VARCHAR (100)      NULL,
    [MapGridUID]                      VARCHAR (100)      NULL,
    [InspectionRequestUID]            VARCHAR (100)      NULL,
    [MasterLeakLogUID]                VARCHAR (100)      NULL,
    [CreatedUserUID]                  VARCHAR (100)      NULL,
    [ModifiedUserUID]                 VARCHAR (100)      NULL,
    [SourceID]                        VARCHAR (100)      NULL,
    [InGridFlag]                      BIT                CONSTRAINT [DF_tgAssetAddressInspection_InGridFlag] DEFAULT ((0)) NULL,
    [srvDTLT]                         DATETIME           CONSTRAINT [DF_tgAssetAddressInspection_srvDTLT] DEFAULT (getdate()) NULL,
    [srvDTLTOffset]                   DATETIMEOFFSET (7) CONSTRAINT [DF_tgAssetAddressInspection_srvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [srcDTLT]                         DATETIME           NULL,
    [Revision]                        INT                CONSTRAINT [DF_tgAssetAddressInspection_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]                      BIT                CONSTRAINT [DF_tgAssetAddressInspection_ActiveFlag] DEFAULT ((1)) NULL,
    [StatusType]                      VARCHAR (200)      CONSTRAINT [DF_tgAssetAddressInspection_StatusType] DEFAULT ('Active') NULL,
    [Latitude]                        FLOAT (53)         NULL,
    [Longitude]                       FLOAT (53)         NULL,
    [GPSSource]                       VARCHAR (20)       NULL,
    [GPSType]                         VARCHAR (20)       NULL,
    [GPSSentence]                     VARCHAR (400)      NULL,
    [GPSTime]                         VARCHAR (10)       NULL,
    [FixQuality]                      INT                NULL,
    [NumberOfSatellites]              INT                NULL,
    [HDOP]                            FLOAT (53)         NULL,
    [AltitudemetersAboveMeanSeaLevel] FLOAT (53)         NULL,
    [HeightofGeoid]                   FLOAT (53)         NULL,
    [TimeSecondsSinceLastDGPS]        FLOAT (53)         NULL,
    [ChecksumData]                    VARCHAR (10)       NULL,
    [Bearing]                         FLOAT (53)         NULL,
    [Speed]                           FLOAT (53)         NULL,
    [GPSStatus]                       VARCHAR (20)       NULL,
    [NumberOfGPSAttempts]             INT                NULL,
    [ActivityUID]                     VARCHAR (100)      NULL,
    [SrcOpenDTLT]                     DATETIME           NULL,
    CONSTRAINT [PK_tgAssetAddressInspection] PRIMARY KEY CLUSTERED ([AssetAddressInspectionID] ASC)
);

