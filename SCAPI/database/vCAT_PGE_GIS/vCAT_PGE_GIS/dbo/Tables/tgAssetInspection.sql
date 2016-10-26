CREATE TABLE [dbo].[tgAssetInspection] (
    [AssetInspectionID]     INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [AssetInspectionUID]    VARCHAR (100)      NULL,
    [AssetUID]              VARCHAR (100)      NULL,
    [MasterLeakLogsUID]     VARCHAR (100)      NULL,
    [MapGridUID]            VARCHAR (100)      NULL,
    [InspectionRequestUID]  VARCHAR (100)      NULL,
    [ProjectID]             INT                NULL,
    [SourceID]              VARCHAR (100)      NULL,
    [CreatedUserUID]        VARCHAR (100)      NULL,
    [ModifiedUserUID]       VARCHAR (100)      NULL,
    [SrcDTLT]               DATETIME           NULL,
    [SrvDTLT]               DATETIME           CONSTRAINT [DF_g_AssetInspections_SrvDTLT] DEFAULT (getdate()) NULL,
    [SrvDTLTOffset]         DATETIMEOFFSET (7) CONSTRAINT [DF_g_AssetInspections_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [SrcOpenDTLT]           DATETIME           NULL,
    [SrcClosedDTLT]         DATETIME           NULL,
    [GPSType]               VARCHAR (100)      NULL,
    [GPSSentence]           VARCHAR (400)      NULL,
    [Latitude]              FLOAT (53)         NULL,
    [Longitude]             FLOAT (53)         NULL,
    [SHAPE]                 [sys].[geography]  NULL,
    [Comments]              VARCHAR (2000)     NULL,
    [RevisionComments]      VARCHAR (500)      NULL,
    [Revision]              INT                CONSTRAINT [DF_g_AssetInspections_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]            BIT                CONSTRAINT [DF_tgAssetInspection_ActiveFlag] DEFAULT ((1)) NULL,
    [StatusType]            VARCHAR (50)       CONSTRAINT [DF_g_AssetInspections_StatusType] DEFAULT ('Active') NULL,
    [InspectionFlag]        BIT                NULL,
    [Photo1]                VARCHAR (250)      NULL,
    [Photo2]                VARCHAR (250)      NULL,
    [Photo3]                VARCHAR (250)      NULL,
    [OptionalData1]         VARCHAR (250)      NULL,
    [OptionalData2]         VARCHAR (250)      NULL,
    [OptionalData3]         VARCHAR (250)      NULL,
    [OptionalData4]         VARCHAR (250)      NULL,
    [OptionalData5]         VARCHAR (250)      NULL,
    [ApprovedFlag]          BIT                NULL,
    [ApprovedByUserUID]     VARCHAR (100)      NULL,
    [ApprovedDTLT]          DATETIME           NULL,
    [SubmittedFlag]         BIT                NULL,
    [SubmittedStatusType]   VARCHAR (200)      NULL,
    [SubmittedUserID]       INT                NULL,
    [SubmittedDTLT]         DATETIME           NULL,
    [ResponseStatusType]    VARCHAR (200)      NULL,
    [ResponseComments]      VARCHAR (500)      NULL,
    [ResponceErrorComments] VARCHAR (500)      NULL,
    [ResponseDTLT]          DATETIME           NULL,
    [CompletedFlag]         BIT                CONSTRAINT [DF_tgAssetInspection_CompletedFlag] DEFAULT ((0)) NULL,
    [CompletedDTLT]         DATETIME           NULL,
    [AdhocFlag]             BIT                CONSTRAINT [DF_tgAssetInspection_AdhocFlag] DEFAULT ((0)) NULL,
    [InspectionFreq]        VARCHAR (20)       CONSTRAINT [DF_tgAssetInspection_InspectionFreq] DEFAULT ('') NULL,
    CONSTRAINT [PK_g_AssetInspections] PRIMARY KEY CLUSTERED ([AssetInspectionID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'g_Asset.AssetUID', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tgAssetInspection', @level2type = N'COLUMN', @level2name = N'AssetUID';


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N't_MasterLeakLogs.MasterLeakLogsUID', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tgAssetInspection', @level2type = N'COLUMN', @level2name = N'MasterLeakLogsUID';


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'g_MapGrids.MapGridUID', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tgAssetInspection', @level2type = N'COLUMN', @level2name = N'MapGridUID';


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N't_InspectionRequests.InspectionRequestUID', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tgAssetInspection', @level2type = N'COLUMN', @level2name = N'InspectionRequestUID';

