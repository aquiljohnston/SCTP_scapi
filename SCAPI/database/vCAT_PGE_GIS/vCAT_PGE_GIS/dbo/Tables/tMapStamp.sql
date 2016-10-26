﻿CREATE TABLE [dbo].[tMapStamp] (
    [MapStampID]           INT           IDENTITY (1, 1) NOT NULL,
    [MapStampUID]          VARCHAR (100) NULL,
    [ProjectID]            INT           NULL,
    [InspectionRequestUID] VARCHAR (100) NULL,
    [CreatedDatetime]      DATETIME      CONSTRAINT [DF_tMapStamp_CreatedDatetime] DEFAULT (getdate()) NULL,
    [StatusType]           VARCHAR (200) NULL,
    [StatusDatetime]       DATETIME      CONSTRAINT [DF_tMapStamp_StatusDatetime] DEFAULT (getdate()) NULL,
    [Revision]             INT           CONSTRAINT [DF_tMapStamp_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]           BIT           NULL,
    [PrintFlag]            BIT           NULL,
    [PrintDateTime]        DATETIME      NULL,
    [ApprovedFlag]         BIT           NULL,
    [ApprovedByUserUID]    VARCHAR (100) NULL,
    [ApprovedDateTime]     DATETIME      NULL,
    [SentFlag]             BIT           NULL,
    [SentDatetime]         DATETIME      NULL,
    [SAPResponse]          VARCHAR (500) NULL,
    [SAPStatusType]        VARCHAR (200) NULL,
    [SAPStatusDateTime]    DATETIME      NULL,
    [Map]                  VARCHAR (10)  NULL,
    [Plat]                 VARCHAR (10)  NULL,
    [InspectionType]       VARCHAR (200) NULL
);

