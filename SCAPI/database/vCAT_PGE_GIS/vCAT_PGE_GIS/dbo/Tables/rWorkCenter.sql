CREATE TABLE [dbo].[rWorkCenter] (
    [rWorkCenterID]              INT            IDENTITY (1, 1) NOT NULL,
    [WorkCenterUID]              VARCHAR (100)  NULL,
    [ProjectID]                  INT            NULL,
    [CreatedUserUID]             VARCHAR (100)  CONSTRAINT [DF_rWorkCenterCreatedUserID] DEFAULT ((999)) NULL,
    [ModifiedUserUID]            VARCHAR (100)  NULL,
    [CreatedDTLT]                DATETIME       CONSTRAINT [DF_rWorkCenterCreateDTLT] DEFAULT (getdate()) NULL,
    [ModifiedDTLT]               DATETIME       NULL,
    [Comments]                   VARCHAR (2000) NULL,
    [RevisionComments]           VARCHAR (500)  NULL,
    [Revision]                   INT            CONSTRAINT [DF_rWorkCenterRevision] DEFAULT ((0)) NOT NULL,
    [ActiveFlag]                 BIT            NOT NULL,
    [StatusType]                 VARCHAR (100)  NULL,
    [WorkCenter]                 VARCHAR (50)   NULL,
    [WorkCenterAbbreviation]     VARCHAR (8)    NULL,
    [WorkCenterAbbreviationFLOC] CHAR (4)       NULL,
    [Division]                   VARCHAR (50)   NULL,
    [DivisionCode]               CHAR (2)       NULL,
    [DivisionNo]                 INT            NULL,
    [Region]                     VARCHAR (50)   NULL,
    [Office]                     VARCHAR (50)   NULL,
    [OfficeAbbreviation]         CHAR (3)       NULL,
    [EZTechClientCode]           VARCHAR (10)   CONSTRAINT [DF_rWorkCenter_EZTechClientCode] DEFAULT ('') NULL,
    CONSTRAINT [PK_rWorkCenter] PRIMARY KEY CLUSTERED ([rWorkCenterID] ASC)
);


GO
EXECUTE sp_addextendedproperty @name = N'MS_Description', @value = N'INF Reference for Work Center and Divisions for PGE', @level0type = N'SCHEMA', @level0name = N'dbo', @level1type = N'TABLE', @level1name = N'rWorkCenter', @level2type = N'COLUMN', @level2name = N'rWorkCenterID';

