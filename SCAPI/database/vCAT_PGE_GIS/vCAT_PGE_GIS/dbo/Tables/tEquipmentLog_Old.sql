﻿CREATE TABLE [dbo].[tEquipmentLog_Old] (
    [tEquipmentLogID]                  INT            IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [EquipmentLogUID]                  VARCHAR (100)  NOT NULL,
    [ProjectID]                        INT            NOT NULL,
    [SourceID]                         VARCHAR (100)  NULL,
    [CreatedUserUID]                   VARCHAR (100)  NOT NULL,
    [ModifiedUserUID]                  VARCHAR (100)  NOT NULL,
    [CreateDTLT]                       DATETIME       CONSTRAINT [DF_t_EquipmentLog_CreateDTLT] DEFAULT (getdate()) NULL,
    [ModifiedDTLT]                     DATETIME       NULL,
    [Comments]                         VARCHAR (2000) NULL,
    [RevisionComments]                 VARCHAR (500)  NULL,
    [Revision]                         INT            CONSTRAINT [DF_t_EquipmentLog_Revision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]                       BIT            CONSTRAINT [DF_t_EquipmentLog_ActiveFlag] DEFAULT ((1)) NOT NULL,
    [SAPPrintFNO]                      VARCHAR (50)   NULL,
    [SAPEquipmentID]                   VARCHAR (100)  NULL,
    [SAPEquipmentType]                 VARCHAR (50)   NULL,
    [SAPEquipmentDescription]          VARCHAR (50)   NULL,
    [SAPSerialNumber]                  VARCHAR (50)   NULL,
    [SAPCalibrationDate]               DATE           NULL,
    [SAPCalibrationTime]               TIME (7)       NULL,
    [SAPEquipmentCalibrationPercent]   FLOAT (53)     NULL,
    [SAPEquipmentCalibrationLowRange]  FLOAT (53)     NULL,
    [SAPEquipmentCalibrationHighRange] FLOAT (53)     NULL,
    [SAPWorkCenter]                    VARCHAR (100)  NULL,
    [SurveyerUserUID]                  VARCHAR (100)  NULL,
    [SupervisorUserUID]                NCHAR (10)     NULL,
    [UsedFlag]                         BIT            NOT NULL,
    [FirstUserUID]                     VARCHAR (100)  NULL,
    [CalibrationLevel]                 VARCHAR (10)   NULL,
    [CalibrationVerificationFlag]      BIT            NULL,
    [MPRFlag]                          BIT            NOT NULL,
    [MPRType]                          VARCHAR (200)  NULL,
    [MPRComments]                      VARCHAR (2000) NULL,
    [SubmittedFlag]                    BIT            NULL,
    [SubmittedStatusType]              VARCHAR (200)  NULL,
    [SubmittedUserUID]                 VARCHAR (100)  NULL,
    [SubmittedDTLT]                    DATETIME       NULL,
    CONSTRAINT [PK_t_Equipment] PRIMARY KEY CLUSTERED ([tEquipmentLogID] ASC)
);

