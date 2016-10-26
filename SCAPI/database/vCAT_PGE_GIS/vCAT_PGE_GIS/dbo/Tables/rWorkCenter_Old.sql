CREATE TABLE [dbo].[rWorkCenter_Old] (
    [rWorkCenterID]          INT           IDENTITY (1, 1) NOT NULL,
    [WorkCenterUID]          VARCHAR (100) NULL,
    [ProjectID]              INT           NULL,
    [CreatedUserUID]         VARCHAR (100) NULL,
    [ModifiedUserUID]        VARCHAR (100) NULL,
    [CreatedDateTime]        DATETIME      NULL,
    [ModifiedDateTime]       DATETIME      NULL,
    [Region]                 VARCHAR (50)  NULL,
    [Division]               VARCHAR (50)  NULL,
    [DCode2]                 VARCHAR (50)  NULL,
    [DCode3]                 VARCHAR (50)  NULL,
    [WorkCenter]             VARCHAR (50)  NULL,
    [MappingOffice]          VARCHAR (50)  NULL,
    [FunctionalLocation]     VARCHAR (100) NULL,
    [WorkCenterAbbreviation] VARCHAR (10)  NULL,
    [Revision]               INT           CONSTRAINT [DF_r_WorkCenter_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]             BIT           CONSTRAINT [DF_r_WorkCenter_ActiveFlag] DEFAULT ((1)) NULL
);

