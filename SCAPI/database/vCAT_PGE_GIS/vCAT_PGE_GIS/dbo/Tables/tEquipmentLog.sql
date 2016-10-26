﻿CREATE TABLE [dbo].[tEquipmentLog] (
    [tEquipmentLogID]     INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [EquipmentLogUID]     VARCHAR (100)  NULL,
    [ProjectID]           INT            NULL,
    [SourceID]            VARCHAR (100)  NULL,
    [CreatedUserUID]      VARCHAR (100)  CONSTRAINT [DF_tEquipmentLogCreatedUserID] DEFAULT ((999)) NULL,
    [ModifiedUserUID]     VARCHAR (100)  NULL,
    [CreateDTLT]          DATETIME       CONSTRAINT [DF_tEquipmentLogCreateDTLT] DEFAULT (getdate()) NULL,
    [ModifiedDTLT]        DATETIME       NULL,
    [Comments]            VARCHAR (2000) NULL,
    [RevisionComments]    VARCHAR (500)  NULL,
    [Revision]            INT            CONSTRAINT [DF_tEquipmentLogRevision] DEFAULT ((0)) NULL,
    [ActiveFlag]          BIT            NULL,
    [PrNtfNo]             VARCHAR (50)   NULL,
    [SAPEqID]             VARCHAR (50)   NULL,
    [EqObjType]           VARCHAR (50)   NULL,
    [EqSerNo]             VARCHAR (50)   NULL,
    [MWC]                 VARCHAR (50)   NULL,
    [CalbDate]            DATE           NULL,
    [LastCalbStat]        VARCHAR (50)   NULL,
    [MPRNo]               VARCHAR (50)   NULL,
    [UpdateFlag]          VARCHAR (50)   NULL,
    [FileCount]           INT            NULL,
    [CalbTime]            TIME (7)       NULL,
    [CalbStat]            VARCHAR (50)   NULL,
    [SrvyLanID]           VARCHAR (50)   NULL,
    [SpvrLanID]           VARCHAR (50)   NULL,
    [CalbHrs]             DECIMAL (6, 2) NULL,
    [FirstUsedFlag]       BIT            NULL,
    [DPIRTestOK]          VARCHAR (50)   NULL,
    [DPIRReadPPM]         DECIMAL (6, 2) NULL,
    [DPIRAlrmPPM]         DECIMAL (6, 2) NULL,
    [RMLDTestOk]          VARCHAR (50)   NULL,
    [RMLDLaserCal]        VARCHAR (50)   NULL,
    [RMLDReadPPM]         DECIMAL (6, 2) NULL,
    [RMLDAlrmPPM]         DECIMAL (6, 2) NULL,
    [FPMKReadPPM]         DECIMAL (6, 2) NULL,
    [OMDExmnQty]          DECIMAL (6, 2) NULL,
    [OMDReadPPM]          DECIMAL (6, 2) NULL,
    [SCOPMethod]          VARCHAR (50)   NULL,
    [SCOPStationPass]     VARCHAR (50)   NULL,
    [SCOPTestKit]         VARCHAR (50)   NULL,
    [SCOPPlelRdg]         DECIMAL (6, 2) NULL,
    [SCOPPgasRdg]         DECIMAL (6, 2) NULL,
    [MPRFlag]             BIT            NULL,
    [MPRRptBy]            VARCHAR (50)   NULL,
    [MPRAsgnTo]           VARCHAR (50)   NULL,
    [MPRLoc]              VARCHAR (50)   NULL,
    [MPRRptDate]          DATE           NULL,
    [MPRCat]              VARCHAR (50)   NULL,
    [MPRDftType]          VARCHAR (50)   NULL,
    [MPRDftOthr]          VARCHAR (50)   NULL,
    [MPRMnf]              VARCHAR (50)   NULL,
    [MPRMatType]          VARCHAR (50)   NULL,
    [MPRMatAge]           VARCHAR (50)   NULL,
    [MPRSftyIsu]          VARCHAR (50)   NULL,
    [MPRMatQty]           VARCHAR (50)   NULL,
    [MPRCausePrblm]       VARCHAR (50)   NULL,
    [MPRDesc]             VARCHAR (150)  NULL,
    [SubmittedFlag]       BIT            NULL,
    [SubmittedStatusType] VARCHAR (200)  NULL,
    [SubmittedUserUID]    VARCHAR (100)  NULL,
    [SubmittedDTLT]       DATETIME       NULL,
    CONSTRAINT [PK_tEquipmentLog] PRIMARY KEY CLUSTERED ([tEquipmentLogID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'INF003 Data Feed from PGE', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'tEquipmentLog', @level2type = N'COLUMN', @level2name = N'tEquipmentLogID';

